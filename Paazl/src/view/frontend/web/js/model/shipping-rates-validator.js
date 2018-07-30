/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

define(
    [
        'jquery',
        'mageUtils',
        'Paazl_Shipping/js/model/shipping-rates-validation-rules',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data',
        'mage/translate'
    ],
    function ($, utils, validationRules, quote, checkoutData, $t) {
        'use strict';
        return {
            validationErrors: [],
            validate: function(address) {
                $.extend(true, {}, quote.shippingAddress(), address);
                var self = this;
                this.validationErrors = [];
                $.each(validationRules.getRules(), function(field, rule) {
                    var addressField = address[field];
                    if (rule.custom_attribute) {
                        if (address.hasOwnProperty('custom_attributes')) {
                            addressField = address['custom_attributes'][field];
                        }
                        else {
                            // Continue
                            return true;
                        }
                    }
                    if (rule.required && utils.isEmpty(addressField)) {
                        var message = $t('Field ') + field + $t(' is required.');
                        self.validationErrors.push(message);
                    }
                });
                return !Boolean(this.validationErrors.length);
            }
        };
    }
);
