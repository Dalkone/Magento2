<?php
namespace WICS\Connector\Controller\Adminhtml\Process;

use WICS\Connector\Helper\ConfigHelper;
use WICS\Connector\Helper\Connectors\HagemanConnector;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;
use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Shipping\Model\Order\TrackFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Shipping\Model\ShipmentNotifier;

class Shipment extends \Magento\Backend\App\Action
{
    /**
     * @var ConfigHelper
     */
    protected $config;

    /**
     * @var HagemanConnector
     */
    protected $hagemanConnector;

    /**
     * @var OrderInterface
     */
    protected $order;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ShipmentFactory
     */
    protected $shipment;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var TrackFactory
     */
    protected $trackFactory;

    /**
     * @var ShipmentNotifier
     */
    protected $shipmentNotifier;

    /**
     * Shipment constructor.
     * @param Action\Context $context
     * @param ConfigHelper $configHelper
     * @param HagemanConnector $hagemanConnector
     * @param OrderInterface $order
     * @param LoggerInterface $_logger
     * @param ShipmentFactory $shipment
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param TrackFactory $trackFactory
     * @param ShipmentNotifier $shipmentNotifier
     */
    public function __construct(
        Action\Context $context,
        ConfigHelper $configHelper,
        HagemanConnector $hagemanConnector,
        OrderInterface $order,
        LoggerInterface $_logger,
        ShipmentFactory $shipment,
        ShipmentRepositoryInterface $shipmentRepository,
        TrackFactory $trackFactory,
        ShipmentNotifier $shipmentNotifier)
    {
        parent::__construct($context);
        $this->config = $configHelper;
        $this->order = $order;
        $this->_logger = $_logger;
        $this->shipment = $shipment;
        $this->shipmentRepository = $shipmentRepository;
        $this->trackFactory = $trackFactory;
        $this->shipmentNotifier = $shipmentNotifier;

        if($this->config->get(ConfigHelper::API_TYPE) == 'hageman') {
            $this->connector = $hagemanConnector;
        }
    }


    /**
     * Fetch shipment for order if available and create local shipment
     *
     * @return mixed
     */
    public function execute() {
        $statusCode = $this->connector->login();

        if($statusCode !== false) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->order->load($this->getRequest()->getParam('order_id'));

            $trackData = $this->connector->getOrderShipment($order);
            if ($trackData) {
                $track = $this->trackFactory->create()
                    ->addData($trackData);
                
                $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
                $shipment = $convertOrder->toShipment($order)->addTrack($track);
                
                foreach($order->getAllItems() AS $orderItem) {
                    // Check if order item has qty to ship or is virtual
                    if(! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                        continue;
                    }

                    $qtyShipped = $orderItem->getQtyToShip();

                    // Create shipment item with qty
                    $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

                    // Add shipment item to shipment
                    $shipment->addItem($shipmentItem);
                }
                
//                $shipment = $this->shipment->create($order)
//                    ->addTrack($track);

                $shipment->register();
                $shipment->getOrder()->setIsInProcess(true)->save();

                if($this->config->get(ConfigHelper::SEND_SHIPPING_EMAIL)) {
                    $this->shipmentNotifier->notify($shipment);
                }

                $shipment->save();

                $this->getMessageManager()->addSuccessMessage($this->connector->getLabel() . ' ' . __("connector: Shipment for order #%1 created",$order->getIncrementId()));
            } else {

                $this->getMessageManager()->addErrorMessage($this->connector->getLabel() . ' ' . __('connector: No shipments available for order %1',$order->getIncrementId()));
            }

        } else {
            $this->getMessageManager()->addErrorMessage($this->connector->getLabel() . ' ' . __('connector: Could not connect to server. Please check your API Key and Secret'));
        }

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}