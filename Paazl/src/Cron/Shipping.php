<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Cron;

use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Paazl\Shipping\Helper\Log as LogHelper;

class Shipping
{
    /** Webshop Identifier config path */
    const XML_PATH_CRON_COMMIT_ORDERS = 'paazl/order/cron_export';

    /** @var \Magento\Sales\Api\OrderRepositoryInterface */
    protected $_orderRepository;

    /** @var \Magento\Sales\Model\OrderFactory */
    protected $_orderFactory;

    /** @var \Magento\Sales\Model\ResourceModel\Order */
    protected $_orderResource;

    /** @var \Paazl\Shipping\Model\Api\RequestBuilder */
    protected $_requestBuilder;

    /** @var \Paazl\Shipping\Model\Api\RequestManager */
    protected $_requestManager;

    /** @var \Magento\Framework\Api\SearchCriteriaBuilder */
    protected $_searchCriteriaBuilder;

    /** @var \Magento\Framework\Api\FilterBuilder */
    protected $_filterBuilder;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfig;

    /**
     * @var \Paazl\Shipping\Model\PaazlManagement
     */
    protected $_paazlManagement;

    /** @var \Psr\Log\LoggerInterface */
    protected $_logger;

    /**
     * @var \Magento\Sales\Model\Convert\OrderFactory
     */
    protected $convertOrderFactory;

    /**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $notifier;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $trackFactory;

    /**
     * @var \Magento\Shipping\Model\Shipping\LabelsFactory
     */
    protected $labelFactory;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $userFactory;

    /** @var LogHelper */
    protected $paazlLog;

    /**
     * CommitOrder constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResource
     * @param \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder
     * @param \Paazl\Shipping\Model\Api\RequestManager $requestManager
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Paazl\Shipping\Model\PaazlManagement $paazlManagement
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory
     * @param \Magento\Shipping\Model\ShipmentNotifier $notifier
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     * @param \Magento\Shipping\Model\Shipping\LabelsFactory $labelFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param LogHelper $log
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResource,
        \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder,
        \Paazl\Shipping\Model\Api\RequestManager $requestManager,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Paazl\Shipping\Model\PaazlManagement $_paazlManagement,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory,
        \Magento\Shipping\Model\ShipmentNotifier $notifier,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Shipping\Model\Shipping\LabelsFactory $labelFactory,
        \Magento\User\Model\UserFactory $userFactory,
        LogHelper $paazlLog
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_orderFactory = $orderFactory;
        $this->_orderResource = $orderResource;
        $this->_requestBuilder = $requestBuilder;
        $this->_requestManager = $requestManager;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_paazlManagement = $_paazlManagement;
        $this->_logger = $logger;
        $this->convertOrderFactory = $convertOrderFactory;
        $this->notifier = $notifier;
        $this->trackFactory = $trackFactory;
        $this->labelFactory = $labelFactory;
        $this->userFactory = $userFactory;
        $this->paazlLog = $paazlLog;
    }

    public function execute()
    {
        if ($this->_scopeConfig->isSetFlag(self::XML_PATH_CRON_COMMIT_ORDERS)) {
            $convertor = $this->convertOrderFactory->create();

            // Get updated orders from Paazl
            $date = new \DateTime();
            $response = $this->_paazlManagement->processListOrdersRequest($date);

            $orders = [];
            if (isset($response['orders']['order'])) $orders = $response['orders']['order'];

            // In case of 1 order, ['orders']['order'] is the first result (object conversion)
            if (!isset($orders[0])) $orders = [$orders];
            foreach ($orders as $order) {
                if (strpos($order['status'], 'LABELS_CREATED') !== false) {
                    $extOrderId = $order['orderReference'];

                    // Check if more than 1 label then get the first label
                    if (!isset($order['label']['trackingNumber'])) {
                        $order['label'] = $order['label'][0];
                    }

                    $trackingNr = $order['label']['trackingNumber'];
                    $shippingOption = $order['shippingOption'];

                    // get order and create a shipment
                    $filterData = [
                        ['ext_order_id', $extOrderId, 'eq']
                    ];
                    $filters = $this->_buildFilters($filterData);
                    $searchCriteria = $this->_buildSearchCriteria($filters);
                    $ordersToCreateShipment = $this->_orderRepository->getList($searchCriteria);
                    $packages = [];
                    foreach ($ordersToCreateShipment as $orderToCreateShipment) {
                        // get the carrier (Paazl or Paazl Perfect)
                        $shippingMethod = $orderToCreateShipment->getShippingMethod();
                        $shippingMethodInfo = explode('_', $shippingMethod);
                        $carrierCode = $shippingMethodInfo[0];

                        if ($orderToCreateShipment->canShip()) {
                            $shipment = $convertor->toShipment($orderToCreateShipment);

                            $items = [];
                            $totalWeigth = 0;
                            // Loop through order items
                            foreach ($orderToCreateShipment->getAllItems() AS $orderItem) {


                                // Check if order item has qty to ship or is virtual
                                if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                                    continue;
                                }

                                $qtyShipped = $orderItem->getQtyToShip();

                                // Create shipment item with qty
                                $shipmentItem = $convertor->itemToShipmentItem($orderItem)->setQty($qtyShipped);

                                // Add shipment item to shipment
                                $shipment->addItem($shipmentItem);
                                $items[] = [
                                    'qty' => $qtyShipped,
                                    'customs_value' => $orderItem->getProduct()->getCustomsMessage(),
                                    'price' => $orderItem->getPrice(),
                                    'name' => $orderItem->getName(),
                                    'weight' => $orderItem->getWeight(),
                                    'product_id' => $orderItem->getProductId(),
                                    'order_item_id' => $orderItem->getItemId(),
                                ];
                                $totalWeigth += $orderItem->getWeight();
                            }
                            // Could also use orderDetails call with orderWeight to get the total weight but this would mean an extra call.
                            $packages[] = [
                                'items' => $items,
                                'params' => [
                                    'container' => '',
                                    'weight' => $totalWeigth,
                                    'custom_value' => '',
                                    'length' => '',
                                    'width' => '',
                                    'height' => '',
                                    'weight_units' => 'KILOGRAM',
                                    'dimension_units' => 'CENTIMETER',
                                    'content_type' => '',
                                    'content_type_other' => '',
                                    'delivery_confirmation' => 'True',
                                ],
                            ];

                            // if we add package and create the shipping label then we don't need to add track data
                            $shipment->setPackages($packages);

                            // Register shipment
                            try {
                                $shipment->register();

                                $shipment->getOrder()->setIsInProcess(true);
                            }
                            catch (\Exception $e) {
                                $this->addPaazlLog(__($e->getMessage()));

                                throw new \Magento\Framework\Exception\LocalizedException(
                                    __($e->getMessage())
                                );
                            }

                            // issue with admin user not logged in from cron in requestToShipment
                            // @todo: maybe let this be configurable.
                            $admin = $this->userFactory->create()->load(1);
                            try {
                                $response = $this->labelFactory->create()->requestToShipmentWithUser($shipment, $admin);
                            }
                            catch (\Exception $e) {
                                $this->addPaazlLog(__($e->getMessage()));

                                throw new \Magento\Framework\Exception\LocalizedException(
                                    __($e->getMessage())
                                );
                            }

                            if ($response->hasErrors()) {
                                $this->addPaazlLog(__($response->getErrors()));
                                throw new \Magento\Framework\Exception\LocalizedException(__($response->getErrors()));
                            }
                            if (!$response->hasInfo()) {
                                $this->addPaazlLog(__('Response info is not exist.'));

                                throw new \Magento\Framework\Exception\LocalizedException(__('Response info is not exist.'));
                            }
                            $labelsContent = [];
                            $trackingNumbers = [];
                            $info = $response->getInfo();
                            foreach ($info as $inf) {
                                if (!empty($inf['tracking_number']) && !empty($inf['label_content'])) {
                                    $labelsContent[] = $inf['label_content'];
                                    $trackingNumbers[] = $inf['tracking_number'];
                                }
                            }

                            $shipment->setShippingLabel($labelsContent[0]);

                            // Add track data
                            $trackData = array(
                                'carrier_code' => $carrierCode,
                                'title' => $shippingOption,
                                'number' => $trackingNr,
                            );

                            $track = $this->trackFactory->create()->addData($trackData);
                            $shipment->addTrack($track)->save();

                            try {
                                // Save created shipment and order
                                $shipment->save();
                                $shipment->getOrder()->save();

                                // Send email
                                $this->notifier->notify($shipment);

                                $shipment->save();
                            } catch (\Exception $e) {
                                // @todo: sometimes the creation of the shipment remains when an error occurs. Maybe thrown somewhere else.
                                $this->addPaazlLog(__($e->getMessage()));

                                throw new \Magento\Framework\Exception\LocalizedException(
                                    __($e->getMessage())
                                );
                            }
                        }
                        else {
                            // @todo: Log that we cannot create shipment
                        }
                    }
                }
            }
        }
    }

    /**
     * [['field', 'value']['field2', 'value2', 'like']]
     *
     * @param array $customFilters
     * @return array
     */
    protected function _buildFilters($customFilters = [])
    {
        $filters = [];

        foreach ($customFilters as $key => $data) {
            list($field, $value, $conditionType) = $data;

            if (is_null($conditionType)) $conditionType = 'eq';

            $newFilter = $this->_filterBuilder
                ->setField($field)
                ->setValue($value)
                ->setConditionType($conditionType)
                ->create();

            $filters[] = $newFilter;
        }

        return $filters;
    }

    /**
     * @param array $filters
     * @return \Magento\Framework\Api\SearchCriteria
     */
    protected function _buildSearchCriteria(array $filters = [])
    {
        $searchCriteria = $this->_searchCriteriaBuilder->addFilters($filters)->create();
        return $searchCriteria;
    }

    protected function addPaazlLog($message)
    {
        $paazlLog = [
            'log_type'  =>  'Cron: paazl_check_shipping',
            'log_code'  =>  0,
            'message'   =>  $message,
            'response_time' => NULL,
        ];
        $this->paazlLog->write($paazlLog);
    }
}
