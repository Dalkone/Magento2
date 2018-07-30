<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Plugin;

class ConvertQuoteAddressToOrderAddress
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
     * @param \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Api\Data\AddressInterface $quoteAddress
     * @param array $data
     * @return \Magento\Sales\Api\Data\OrderAddressInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject,
        \Closure $proceed,
        \Magento\Quote\Api\Data\AddressInterface $quoteAddress,
        $data = []
    ) {
        $orderAddress = $proceed($quoteAddress, $data);
        $attributes = $this->customerData->getCustomerAddressUserDefinedAttributeCodes();
        foreach ($attributes as $attribute) {
            $orderAddress->setData($attribute, $quoteAddress->getData($attribute));
        }
        return $orderAddress;
    }
}
