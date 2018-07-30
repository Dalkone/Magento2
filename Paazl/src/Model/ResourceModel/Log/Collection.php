<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\ResourceModel\Log;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Paazl\Shipping\Model\ResourceModel\Log
 */
class Collection extends AbstractCollection
{
    /**
    * Define resource model
    *
    * @return void
    */
    protected function _construct() {
        $this->_init('Paazl\Shipping\Model\Log', 'Paazl\Shipping\Model\ResourceModel\Log');
    }
}
