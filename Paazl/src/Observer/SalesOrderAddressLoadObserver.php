<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderAddressLoadObserver implements ObserverInterface
{
    /**
     * @var \Paazl\Shipping\Helper\Utility\Address
     */
    protected $addressHelper;

    /**
     * CustomerAddressLoadObserver constructor.
     * @param \Paazl\Shipping\Helper\Utility\Address $addressHelper
     */
    public function __construct(
        \Paazl\Shipping\Helper\Utility\Address $addressHelper
    )
    {
        $this->addressHelper = $addressHelper;
    }


    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Address $address */
        $address = $observer->getEvent()->getAddress();
        $houseNumberFull = '';

        // convert old address to new format
        $streetParts = $this->addressHelper->getMultiLineStreetParts($address->getStreet());
        if (!$streetParts['house_number']) {
            // Get street, house number, etc from line 1
            $streetParts = $this->addressHelper->getStreetParts($address->getStreet());
        }
        if ($address->getStreetName() != '') {
            $streetParts['street'] = $address->getStreetName();
        }
        if ($address->getHouseNumber() != '') {
            $streetParts['house_number'] = $address->getHouseNumber();
        }
        if ($address->getHouseNumberAddition() != '') {
            $streetParts['addition'] = $address->getHouseNumberAddition();
        }
        $houseNumberFull = $streetParts['house_number'];
        if ($streetParts['addition'] != '') {
            $houseNumberFull .= ' ' . $streetParts['addition'];
        }

        // @todo: check if already has values for house_number, etc
        $address->setStreetName($streetParts['street']);
        $address->setHouseNumber($streetParts['house_number']);
        $address->setHouseNumberAddition($streetParts['addition']);

        $address->setStreet($streetParts['street'] . " " . $houseNumberFull);
    }
}
