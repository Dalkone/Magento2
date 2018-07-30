/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Paazl_Shipping/js/model/shipping-rates-validator',
        'Paazl_Shipping/js/model/shipping-rates-validation-rules',
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        shippingRatesValidator,
        shippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('paazl', shippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('paazl', shippingRatesValidationRules);
        defaultShippingRatesValidator.registerValidator('paazlp', shippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('paazlp', shippingRatesValidationRules);

        return Component;
    }
);
