<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Setup;

use Magento\Eav\Setup\EavSetup;

class PaazlSetup extends EavSetup
{
    public function getAttributeList()
    {
        return [
            [
                'attributeCode' => 'length',
                'label' => 'Length',
            ],
            [
                'attributeCode' => 'width',
                'label' => 'Width',
            ],
            [
                'attributeCode' => 'height',
                'label' => 'Height',
            ],
            [
                'attributeCode' => 'volume',
                'label' => 'Volume',
            ],
            [
                'attributeCode' => 'customs_message',
                'label' => 'Customs message',
            ],
            [
                'attributeCode' => 'matrix',
                'label' => 'Matrix override',
            ],
        ];
    }
}