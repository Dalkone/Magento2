<?php
namespace WICS\Connector\Observer;

use WICS\Connector\Helper\ConfigHelper;
use WICS\Connector\Helper\Connectors\HagemanConnector;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class ProcessOrders implements \Magento\Framework\Event\ObserverInterface {
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var HagemanConnector
     */
    protected $hagemanConnector;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var
     */
    protected $connector;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * ProcessOrders constructor.
     *
     * @param ConfigHelper $configHelper
     * @param HagemanConnector $hagemanConnector
     * @param LoggerInterface $_logger
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ConfigHelper $configHelper,
        HagemanConnector $hagemanConnector,
        LoggerInterface $_logger,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->config = $configHelper;
        $this->_logger = $_logger;
        $this->orderRepository = $orderRepository;

        if($this->config->get(ConfigHelper::API_TYPE) == "hageman") {
            $this->connector = $hagemanConnector;
        }
    }

    /**
     * Send orders to WICS when order status matches one of the watched statuses
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        /* Retrieve current order to prevent order objects to overtake each other */
        $_order = $this->orderRepository->get($order->getId());

        $orderstatesToMonitor = explode(",", $this->config->get(ConfigHelper::STATUS_CODES));

        if($order->getOrigData("state") != $order->getData("state") &&
            in_array($order->getData("state"), $orderstatesToMonitor) &&
            $this->config->get(ConfigHelper::ENABLE_ORDERS) &&
            $_order->getWics_status() == "" &&
            $order->getData("wicsProcessed") != true
        ) {
            $this->_logger->debug($this->connector->getLabel() . " connector: Order processing event trigered");

            /* fix to prevent same order from processing twice */
            $order->setData("wicsProcessed", true);
            
            /* Login */
            $statusCode = $this->connector->login();
            if($statusCode !== false) {
                $statusCode = $this->connector->putOrder($order);
                if($statusCode === true) {
                    $this->_logger->debug($this->connector->getLabel() . " connector: Order #" . $order->getIncrementId() . " successful sent");
                } else {
                    $this->_logger->debug($this->connector->getLabel() . " connector: Order #" . $order->getIncrementId() . " failed with statuscode: " . $statusCode);
                }

            } else {
                $this->_logger->debug($this->connector->getLabel() . " connector: Could not connect to remote server");
            }
        } else {

            $this->_logger->debug($this->connector->getLabel() . " connector: Order processing requirements not met");
        }
    }
}