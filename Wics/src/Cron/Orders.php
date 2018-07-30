<?php
namespace WICS\Connector\Cron;

use Psr\Log\LoggerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use WICS\Connector\Helper\Connectors\HagemanConnector;
use WICS\Connector\Helper\ConfigHelper;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Shipping\Model\Order\TrackFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;


class Orders {
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
     * Orders constructor.
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param HagemanConnector $hagemanConnector
     * @param ConfigHelper $config
     * @param ShipmentFactory $shipment
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param TrackFactory $trackFactory
     */
    public function __construct(
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        HagemanConnector $hagemanConnector,
        ConfigHelper $config,
        ShipmentFactory $shipment,
        ShipmentRepositoryInterface $shipmentRepository,
        TrackFactory $trackFactory
    ) {
        $this->_logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config = $config;
        $this->shipment = $shipment;
        $this->shipmentRepository = $shipmentRepository;
        $this->trackFactory = $trackFactory;

        if($this->config->get(ConfigHelper::API_TYPE) == 'hageman') {
            $this->connector = $hagemanConnector;
        }
    }

    /**
     * Check for pending orders, and post them to WICS
     *
     * @return $this
     */
    public function execute() {
        if($this->config->get(ConfigHelper::ENABLE_ORDERS)) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('wics_status', 'pending', 'eq')->create();
            $pendingOrders = $this->orderRepository->getList($searchCriteria);

            if($pendingOrders) {
                $statusCode = $this->connector->login();

                if($statusCode === true) {
                    foreach($pendingOrders as $order) {
                        $this->connector->putOrder($order);
                    }
                }
            }

            $this->_logger->debug($this->connector->getLabel() . ' Order importer cron run');
        }

        return $this;
    }
}