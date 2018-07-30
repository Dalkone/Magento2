<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Quote;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Quote\Api\Data\AddressInterface;

class Address extends \Magento\Quote\Model\Quote\Address
{
    /**
     * Validate address attribute values
     *
     * @return bool|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate()
    {
        $errors = [];
        if (!\Zend_Validate::is($this->getFirstname(), 'NotEmpty')) {
            $errors[] = __('Please enter the first name.');
        }

        if (!\Zend_Validate::is($this->getLastname(), 'NotEmpty')) {
            $errors[] = __('Please enter the last name.');
        }

        if (!\Zend_Validate::is($this->getCity(), 'NotEmpty')) {
            $errors[] = __('Please enter the city.');
        }

        if (!\Zend_Validate::is($this->getTelephone(), 'NotEmpty')) {
            $errors[] = __('Please enter the phone number.');
        }

        $_havingOptionalZip = $this->_directoryData->getCountriesWithOptionalZip();
        if (!in_array(
                $this->getCountryId(),
                $_havingOptionalZip
            ) && !\Zend_Validate::is(
                $this->getPostcode(),
                'NotEmpty'
            )
        ) {
            $errors[] = __('Please enter the zip/postal code.');
        }

        if (!\Zend_Validate::is($this->getCountryId(), 'NotEmpty')) {
            $errors[] = __('Please enter the country.');
        }

        if ($this->getCountryModel()->getRegionCollection()->getSize() && !\Zend_Validate::is(
                $this->getRegionId(),
                'NotEmpty'
            ) && $this->_directoryData->isRegionRequired(
                $this->getCountryId()
            )
        ) {
            $errors[] = __('Please enter the state/province.');
        }

        if (empty($errors) || $this->getShouldIgnoreValidation()) {
            return true;
        }
        return $errors;
    }
}