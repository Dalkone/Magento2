<?php
namespace WICS\Connector\Observer;

use WICS\Connector\Helper\ConfigHelper;
use WICS\Connector\Helper\Connectors\HagemanConnector;
use Psr\Log\LoggerInterface;
use Magento\Framework\Message\ManagerInterface;

class ConfigObserver implements \Magento\Framework\Event\ObserverInterface {
    /**
     * @var ConfigHelper
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
     * @var ManagerInterface
     */
    protected $_message;

    /**
     * ConfigObserver constructor.
     *
     * @param ConfigHelper $configHelper
     * @param HagemanConnector $hagemanConnector
     * @param LoggerInterface $_logger
     * @param ManagerInterface  $_message
     */
    public function __construct(
        ConfigHelper $configHelper,
        HagemanConnector $hagemanConnector,
        LoggerInterface $_logger,
        ManagerInterface  $_message)
    {
        $this->config = $configHelper;
        $this->_logger = $_logger;
        $this->_message = $_message;

        if ( $this->config->get(ConfigHelper::API_TYPE) == "hageman") {
            $this->connector = $hagemanConnector;
        }

    }

    public function execute(\Magento\Framework\Event\Observer $observer) {

        if ( $this->config->get(ConfigHelper::API_KEY) == "" ||
            $this->config->get(ConfigHelper::API_SECRET) == "" ||
            $this->config->get(ConfigHelper::API_CLIENT) == "" ||
            $this->config->get(ConfigHelper::API_TYPE) == "" 
        ) {
            $this->_message->addNoticeMessage($this->connector->getLabel() . " " . __("connector: Not all required fields are filled in."));
            return;
        }

        $statusCode = $this->connector->login();
        if($statusCode === false) {
            $this->_message->addErrorMessage($this->connector->getLabel() . " " . __("connector: Could not connect to server. Please check your API Key and Secret"));
            return;
        } else {
            $this->_message->addSuccessMessage($this->connector->getLabel() . " " .__("connector: Successful connected to server."));
            return;
        }

    }
}