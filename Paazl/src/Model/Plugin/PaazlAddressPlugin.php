<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

namespace Paazl\Shipping\Model\Plugin;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Paazl\Shipping\Helper\Utility\Address;
class PaazlAddressPlugin
{

    /**
     * @var \Paazl\Shipping\Helper\Utility\Address
     */
    protected $addressHelper;

    /**
     * CustomerAddressLoadObserver constructor.
     *
     * @param \Paazl\Shipping\Helper\Utility\Address $addressHelper
     */
    public function __construct(
        Address $addressHelper
    ) {
        $this->addressHelper = $addressHelper;
    }
    /**
     * @param OrderAddressInterface $address
     * @param array $street
     *
     * @return []
     */
    public function afterGetStreet(OrderAddressInterface $address, $street)
    {
        if (!$this->isMigratedStreet($address)) {
            $this->migrateStreet($address, $street);
        }
        return $this->getFormattedStreet($address);
    }


    /**
     * @param OrderAddressInterface $address
     *
     * @return bool
     */
    public function isMigratedStreet(OrderAddressInterface $address)
    {
        return false;
        //return $this->getData('street') == $this->getFormattedStreet($address);

    }


    /**
     * @param $address
     * @param $street
     */
    public function migrateStreet(OrderAddressInterface $address, $street)
    {
        $convertedStreet = $this->addressHelper->getMultiLineStreetParts($street);
        if (!$convertedStreet['house_number']) {
            $convertedStreet = $this->addressHelper->getStreetParts($street);
        }
        $extensionAttributes = $address->getExtensionAttributes();
        $extensionAttributes->setStreetName($convertedStreet['street']);
        $extensionAttributes->setHouseNumber($convertedStreet['house_number']);
        $extensionAttributes->setHouseNumberAddition($convertedStreet['addition']);
    }


    /**
     * @param OrderAddressInterface $address
     *
     * @return string
     */
    function getFormattedStreet(OrderAddressInterface $address) {
        return sprintf(
            "%s\n%s %s",
            $address->getExtensionAttributes()->getStreetName(),
            $address->getExtensionAttributes()->getHouseNumber(),
            $address->getExtensionAttributes()->getHouseNumberAddition()
        );
    }

    function beforeSave(\Magento\Sales\Model\Order\Address $address){
        $address->setData('street_name', $address->getData('extension_attributes')->getStreetName());
        $address->setData('house_number', $address->getData('extension_attributes')->getHouseNumber());
        $address->setData('house_number_addition', $address->getData('extension_attributes')->getHouseNumberAddition());
    }
}
