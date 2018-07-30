<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

/**
 * Class LayoutProcessor
 * @package Paazl\Shipping\Block\Checkout
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;

    /**
     * @var \Magento\Ui\Component\Form\AttributeMapper
     */
    protected $attributeMapper;

    /**
     * @var \Magento\Checkout\Block\Checkout\AttributeMerger
     */
    protected $merger;

    /**
     * @param \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param \Magento\Ui\Component\Form\AttributeMapper $attributeMapper
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $merger
     */
    public function __construct(
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Ui\Component\Form\AttributeMapper $attributeMapper,
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->merger = $merger;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        // Start: Maybe check if EE is installed, then this part is not needed
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );
        $addressElements = [];
        foreach ($attributes as $attribute) {
            if (!$attribute->getIsUserDefined()) {
                continue;
            }
            $addressElements[$attribute->getAttributeCode()] = $this->attributeMapper->map($attribute);
            $addressElements[$attribute->getAttributeCode()]['label'] = __($addressElements[$attribute->getAttributeCode()]['label']);
        }

        // The following code is a workaround for custom address attributes
        $paymentMethodRenders = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['payments-list']['children'];
        if (is_array($paymentMethodRenders)) {
            foreach ($paymentMethodRenders as $name => $renderer) {
                if (isset($renderer['children']) && array_key_exists('form-fields', $renderer['children'])) {
                    $fields = $renderer['children']['form-fields']['children'];
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                    ['children']['payment']['children']['payments-list']['children'][$name]['children']
                    ['form-fields']['children'] = $this->merger->merge(
                        $addressElements,
                        'checkoutProvider',
                        $renderer['dataScopePrefix'] . '.custom_attributes',
                        $fields
                    );

                    $formFields = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                    ['children']['payment']['children']['payments-list']['children'][$name]['children']
                    ['form-fields']['children'];
                    if (isset($formFields['postcode']) && isset($formFields['street'])) {
                        // Remove old street from checkout form
                        unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                            ['children']['payment']['children']['payments-list']['children'][$name]['children']
                            ['form-fields']['children']['street']);
                    }
                }
            }
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
        )) {
            $fields = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = $this->merger->merge(
                $addressElements,
                'checkoutProvider',
                'shippingAddress.custom_attributes',
                $fields
            );
        }
        // End: Maybe check if EE is installed, then this part is not needed

        $addressElements = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

        if (isset($addressElements['postcode']) && isset($addressElements['street'])) {
            unset($addressElements['street']);

            // Config of existing fields to overwrite
            $elementConfig = [
                'postcode' => [
                    'sortOrder' => (int)$addressElements['house_number_addition']['sortOrder'] - 1,
                ],
            ];
            $addressElements = array_replace_recursive($addressElements, $elementConfig);

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = $addressElements;
        }

        return $jsLayout;
    }
}
