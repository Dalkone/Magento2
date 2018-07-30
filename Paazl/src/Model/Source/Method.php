<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Source;

class Method implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'a', 'label' => __('A')],
            ['value' => 'b', 'label' => __('B')]
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return ['a' => __('A'), 'b' => __('B')];
    }
}
