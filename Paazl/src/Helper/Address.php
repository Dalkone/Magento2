<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

/**
 * Customer Data Helper
 *
 */
namespace Paazl\Shipping\Helper;

class Address extends \Paazl\Shipping\Helper\Data
{
    /**
     * Default attribute entity type code
     *
     * @return string
     */
    protected function _getEntityTypeCode()
    {
        return 'customer_address';
    }

    /**
     * Return available customer address attribute form as select options
     *
     * @return array
     */
    public function getAttributeFormOptions()
    {
        return [
            ['label' => __('Customer Address Registration'), 'value' => 'customer_register_address'],
            ['label' => __('Customer Account Address'), 'value' => 'customer_address_edit']
        ];
    }
}
