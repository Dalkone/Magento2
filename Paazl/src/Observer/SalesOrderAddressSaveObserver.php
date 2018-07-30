<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderAddressSaveObserver implements ObserverInterface
{
    /**
     * @var \Paazl\Shipping\Helper\Utility\Address
     */
    protected $addressHelper;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Sales\Api\OrderAddressRepositoryInterface
     */
    protected $salesAddressRepository;

    /**
     * CustomerAddressSaveObserver constructor.
     * @param \Paazl\Shipping\Helper\Utility\Address $addressHelper
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Sales\Api\OrderAddressRepositoryInterface  $salesAddressRepository
     */
    public function __construct(
        \Paazl\Shipping\Helper\Utility\Address $addressHelper,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Sales\Api\OrderAddressRepositoryInterface $salesAddressRepository
    )
    {
        $this->addressHelper = $addressHelper;
        $this->addressRepository = $addressRepository;
        $this->salesAddressRepository = $salesAddressRepository;
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

        // Check if this is a saved address, then use those values. $streetParts could be incorrect when the address has a comma in it.
        if ($address->hasData('customer_address_id') && is_numeric($address->getCustomerAddressId())) {
            $customerAddress = $this->addressRepository->getById($address->getCustomerAddressId());

            $streetParts['street'] = $customerAddress->getCustomAttribute('street_name')->getValue();
            $streetParts['house_number'] = $customerAddress->getCustomAttribute('house_number')->getValue();
            $streetParts['addition'] = $customerAddress->getCustomAttribute('house_number_addition')->getValue();
        }
        // Load address directly. Address from observer event misses data.
        else {
            if ($address->hasData('entity_id')) {
                $salesAddress = $this->salesAddressRepository->get($address->getEntityId());
                $streetParts['street'] = $salesAddress->getStreetName();
                $streetParts['house_number'] = $salesAddress->getHouseNumber();
                $streetParts['addition'] = $salesAddress->getHouseNumberAddition();
            }
        }

        // @todo: check if already has values for house_number, etc
        $address->setStreetName($streetParts['street']);
        $address->setHouseNumber($streetParts['house_number']);
        $address->setHouseNumberAddition($streetParts['addition']);

        $address->setStreet($streetParts['street'] . " " . $houseNumberFull);
    }
}
