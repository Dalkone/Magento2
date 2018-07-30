<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Source;

class ApiType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Staging')],
            ['value' => 1, 'label' => __('Production')]
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Staging'), 1 => __('Production')];
    }
}
