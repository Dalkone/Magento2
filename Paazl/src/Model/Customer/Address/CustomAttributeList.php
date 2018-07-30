<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Customer\Address;

class CustomAttributeList implements \Magento\Customer\Model\Address\CustomAttributeListInterface
{
    /**
     * @var \Magento\Customer\Api\AddressMetadataInterface
     */
    protected $addressMetadata;

    /**
     * @var \Magento\Framework\Api\MetadataObjectInterface[]
     */
    protected $attributes = null;

    /**
     * @param \Magento\Customer\Api\AddressMetadataInterface $addressMetadata
     */
    public function __construct(\Magento\Customer\Api\AddressMetadataInterface $addressMetadata)
    {
        $this->addressMetadata = $addressMetadata;
    }

    /**
     * Retrieve list of quote address custom attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        if ($this->attributes === null) {
            $this->attributes = [];
            $customAttributesMetadata = $this->addressMetadata->getCustomAttributesMetadata(
                '\Magento\Customer\Api\Data\AddressInterface'
            );
            if (is_array($customAttributesMetadata)) {
                /** @var $attribute \Magento\Framework\Api\MetadataObjectInterface */
                foreach ($customAttributesMetadata as $attribute) {
                    $this->attributes[$attribute->getAttributeCode()] = $attribute;
                }
            }
            $customAttributesMetadata = $this->addressMetadata->getCustomAttributesMetadata(
                '\Magento\Customer\Api\Data\CustomerInterface'
            );
            if (is_array($customAttributesMetadata)) {
                /** @var $attribute \Magento\Framework\Api\MetadataObjectInterface */
                foreach ($customAttributesMetadata as $attribute) {
                    $this->attributes[$attribute->getAttributeCode()] = $attribute;
                }
            }
        }
        return $this->attributes;
    }
}
