<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Paazl\Shipping\Model\LogFactory;
use Paazl\Shipping\Model\LogRepository;

/**
 * Class Log
 * @package Paazl\Shipping\Helper
 */
class Log extends AbstractHelper
{
    const XML_PATH_STORECONFIGURATION_PAAZL_LOG = 'paazl/debug/log';

    /** @var  LogFactory */
    protected $logObject;

    /** @var  LogRepository */
    protected $logRepository;

    /** @var ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;

    /**
     * Log constructor.
     * @param Context $context
     * @param DateTime $_date
     * @param LogRepository $logRepository
     * @param LogFactory $logFactory
     */
    public function __construct(
        Context $context,
        DateTime $_date,
        LogRepository $logRepository,
        LogFactory $logFactory
    ) {
        parent::__construct($context);

        $this->logRepository = $logRepository;
        $this->logObject = $logFactory;
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Write the log entry to the database if logging is enabled
     * @param $data
     * @return bool
     */
    public function write($data) {
        if($this->getLoggingEnabled()) {
            $log = $this->logObject->create();
            $log->setData($data);

            $result = $this->logRepository->save($log);
            if ($result)
                return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function getLoggingEnabled() {
        $storeScope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_LOG, $storeScope);
    }
}
