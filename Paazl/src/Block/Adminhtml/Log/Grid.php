<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Block\Adminhtml\Log;

/**
 * Class Grid
 * @package Paazl\Shipping\Block\Adminhtml\Log
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Paazl_Shipping';
        $this->_controller = 'adminhtml_log';
        $this->_headerText = __('Paazl Log');

        parent::_construct();
        $this->buttonList->remove('add');
    }
}
