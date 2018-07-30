<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class OrderSaveObserver implements ObserverInterface
{
    /** Valid commit status config path */
    const XML_PATH_ORDER_COMMIT_STATUSES = 'paazl/order/commit_statuses';

    /** @var array */
    protected $commitStatuses;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfig;

    /** @var \Paazl\Shipping\Model\Api\RequestBuilder */
    protected $_requestBuilder;

    /** @var \Paazl\Shipping\Model\Api\RequestManager */
    protected $_requestManager;

    /**
     * OrderSaveObserver constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder
     * @param \Paazl\Shipping\Model\Api\RequestManager $requestManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder,
        \Paazl\Shipping\Model\Api\RequestManager $requestManager,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_requestBuilder = $requestBuilder;
        $this->_requestManager = $requestManager;
        if (isset($data['commit_statuses'])) $this->commitStatuses = $data['commit_statuses'];
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        if (!$order->getIsVirtual()) {
            if (is_null($order->getExtOrderId())) {
                if (is_null($this->commitStatuses)) {
                    $this->commitStatuses = explode(',', $this->_scopeConfig->getValue(self::XML_PATH_ORDER_COMMIT_STATUSES));
                }
                if (in_array($order->getStatus(), $this->commitStatuses) || !($order->getTotalDue() > 0)) {
                    // Set external order ID to 0, queueing it for export
                    $order->setExtOrderId(0);
                }
            }
        }
    }
}
