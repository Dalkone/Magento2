<?php
namespace WICS\Connector\Cron;

use Psr\Log\LoggerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use WICS\Connector\Helper\Connectors\HagemanConnector;
use WICS\Connector\Helper\ConfigHelper;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Shipping\Model\Order\TrackFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Shipping\Model\ShipmentNotifier;
use Magento\Sales\Api\Data\OrderInterface;


class Shipments {
    /**
     * @var ObjectManager
     */
    private $_objectManager;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var HagemanConnector
     */
    protected $hagemanConnector;

    /**
     * @var ConfigHelper
     */
    protected $config;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var TrackFactory
     */
    protected $trackFactory;

    /**
     * @var OrderInterface
     */
    protected $order;

    /**
     * Shipments constructor.
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param HagemanConnector $hagemanConnector
     * @param ConfigHelper $config
     * @param ShipmentFactory $shipment
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param TrackFactory $trackFactory
     * @param ShipmentNotifier $shipmentNotifier
     * @param OrderInterface $order
     */
    public function __construct(
        ObjectManagerInterface $objectmanager,
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        HagemanConnector $hagemanConnector,
        ConfigHelper $config,
        ShipmentFactory $shipment,
        ShipmentRepositoryInterface $shipmentRepository,
        TrackFactory $trackFactory,
        ShipmentNotifier $shipmentNotifier,
        OrderInterface $order
    ) {
        $this->_objectManager = $objectmanager;
        $this->_logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config = $config;
        $this->shipment = $shipment;
        $this->shipmentRepository = $shipmentRepository;
        $this->trackFactory = $trackFactory;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->order = $order;

        if ( $this->config->get(ConfigHelper::API_TYPE) == "hageman") {
            $this->connector = $hagemanConnector;
        }

    }

    /**
     * @return $this
     */
    public function execute() {
        if($this->config->get(ConfigHelper::ENABLE_ORDERS)) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter("wics_status", "processing", "eq")->create();
            $pendingOrders = $this->orderRepository->getList($searchCriteria);

            $this->_logger->debug($this->connector->getLabel() . " Shipment slips importer started");

            if($pendingOrders) {
                $statusCode = $this->connector->login();

                if($statusCode === true) {
                    foreach($pendingOrders as $_order) {
                        $order = $this->order->load($_order->getId());
                        $trackData = $this->connector->getOrderShipment($order);

                        if($trackData) {
                            $track = $this->trackFactory->create()
                                ->addData($trackData);
                            
                            $convertOrder = $this->_objectManager->create("Magento\Sales\Model\Convert\Order");
                            $shipment = $convertOrder->toShipment($order)->addTrack($track);

                            foreach($order->getAllItems() as $orderItem) {
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

                            $shipment->register();
                            $shipment->getOrder()->setIsInProcess(true)->save();

                            if($this->config->get(ConfigHelper::SEND_SHIPPING_EMAIL)) {
                                $this->shipmentNotifier->notify($shipment);
                            }

                            $shipment->save();

                        }
                    }
                } else {
                    $this->_logger->debug($this->connector->getLabel() . " Shipment slips importer Cant login");
                }

                $this->_logger->debug($this->connector->getLabel() . " Shipment slips importer finished");
            }
        }

        return $this;
    }
}