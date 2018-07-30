<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Controller\Adminhtml\Log;

use Paazl\Shipping\Controller\Adminhtml\Log;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 * @package Paazl\Shipping\Controller\Adminhtml\Log
 */
class Index extends Log
{
    /**
     * @return $this
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Paazl Log'));
        return $resultPage;
    }
}
