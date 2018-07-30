<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Customer\Metadata;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;

class Form extends \Magento\Customer\Model\Metadata\Form
{
    /**
     * Get validator
     *
     * @param array $data
     * @return \Magento\Framework\Validator
     */
    protected function _getValidator(array $data)
    {
        if ($this->_validator !== null) {
            return $this->_validator;
        }

        $configFiles = $this->_modulesReader->getConfigurationFiles('validation.xml');
        $validatorFactory = $this->_validatorConfigFactory->create(['configFiles' => $configFiles]);
        $builder = $validatorFactory->createValidatorBuilder('customer', 'form');

        $attributes = $this->getAllowedAttributes();
        // Remove old street field in forms
        unset($attributes['street']);

        $builder->addConfiguration(
            'metadata_data_validator',
            ['method' => 'setAttributes', 'arguments' => [$attributes]]
        );
        $builder->addConfiguration(
            'metadata_data_validator',
            ['method' => 'setData', 'arguments' => [$data]]
        );
        $builder->addConfiguration(
            'metadata_data_validator',
            ['method' => 'setEntityType', 'arguments' => [$this->_entityType]]
        );
        $this->_validator = $builder->createValidator();

        return $this->_validator;
    }
}