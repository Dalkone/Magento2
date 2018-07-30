<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Plugin;

class ConvertQuoteAddressToCustomerAddress
{
    /**
     * @var \Paazl\Shipping\Helper\Data
     */
    private $customerData;

    /**
     * @param \Paazl\Shipping\Helper\Data $customerData
     */
    public function __construct(
        \Paazl\Shipping\Helper\Data $customerData
    ) {
        $this->customerData = $customerData;
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $quoteAddress
     * @param \Magento\Customer\Api\Data\AddressInterface $customerAddress
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function afterExportCustomerAddress(
        \Magento\Quote\Api\Data\AddressInterface $quoteAddress,
        \Magento\Customer\Api\Data\AddressInterface $customerAddress
    ) {
        $attributes = $this->customerData->getCustomerAddressUserDefinedAttributeCodes();
        foreach ($attributes as $attribute) {
            $customerAddress->setCustomAttribute($attribute, $quoteAddress->getData($attribute));
        }
        return $customerAddress;
    }
}
