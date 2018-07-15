<?php
namespace WICS\Connector\Controller\Adminhtml\Process;

use WICS\Connector\Helper\ConfigHelper;
use WICS\Connector\Helper\Connectors\HagemanConnector;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;
use Magento\Backend\App\Action;

class Order extends \Magento\Backend\App\Action
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
     * @var
     */
    protected $_resultFactory;

    /**
     * @var
     */
    protected $connector;

    /**
     * Order constructor.
     *
     * @param Action\Context $context
     * @param ConfigHelper $configHelper
     * @param HagemanConnector $hagemanConnector
     * @param OrderInterface $order
     * @param LoggerInterface $_logger
     */
    public function __construct(
        Action\Context $context,
        ConfigHelper $configHelper,
        HagemanConnector $hagemanConnector,
        OrderInterface $order,
        LoggerInterface $_logger)
    {
        parent::__construct($context);
        $this->config = $configHelper;
        $this->order = $order;
        $this->_logger = $_logger;

        if($this->config->get(ConfigHelper::API_TYPE) == 'hageman') {
            $this->connector = $hagemanConnector;
        }
    }

    /**
     * manually send selected order to WICS
     *
     * @return mixed
     */
    public function execute() {
        $order = $this->order->load($this->getRequest()->getParam('order_id'));

        $orderStatesToMonitor = explode(',', $this->config->get(ConfigHelper::STATUS_CODES));

        if(in_array($order->getData('state'), $orderStatesToMonitor) && !in_array($order->getWics_status(),['processing','complete'])) {
            $statusCode = $this->connector->login();

            if($statusCode !== false) {
                $this->_logger->debug('WICS connector: Login success');

                //Login success, send test order
                $statusCode = $this->connector->putOrder($order);

                if($statusCode ) {
                    $this->_logger->debug('WICS connector: Order #' . $order->getIncrementId() . ' successful sent to WICS');
                    $this->getMessageManager()->addSuccessMessage($this->connector->getLabel() . ' ' . __('connector: The order has been successfully sent to ') . $this->connector->getLabel());

                } else {
                    $this->getMessageManager()->addErrorMessage($this->connector->getLabel() . ' ' . __('connector: Failed to sent the order to ') . $this->connector->getLabel() . ': ' . implode("<br>", $this->connector->getErrors()));
                }
            } else {
                $this->_logger->error('WICS connector: Could not connect to remote server');
                $this->getMessageManager()->addErrorMessage($this->connector->getLabel() . ' ' . __('connector: Could not connect to server. Please check your API Key and Secret'));
            }
        }

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}