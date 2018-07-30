<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Source\Method;

class Generic
{
    /** @var \Paazl\Shipping\Model\Carrier */
    protected $paazlCarrier;

    /** @var string */
    protected $_code = '';

    /**
     * @param \Paazl\Shipping\Model\Carrier $carrier
     */
    public function __construct(\Paazl\Shipping\Model\Carrier $carrier)
    {
        $this->paazlCarrier = $carrier;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $configData = $this->paazlCarrier->getCode($this->_code);
        $arr = [];
        foreach ($configData as $code => $title) {
            $arr[] = ['value' => $code, 'label' => $title];
        }
        return $arr;
    }
}
