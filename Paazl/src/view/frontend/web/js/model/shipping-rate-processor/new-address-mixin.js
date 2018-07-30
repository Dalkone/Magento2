/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/checkout-data'
], function ($, wrapper, checkoutData) {
    'use strict';

    return function (defaultProcessor) {
        defaultProcessor.getRates = wrapper.wrap(defaultProcessor.getRates, function (originalAction, address) {
            var streetName = $("input[name='custom_attributes[street_name]']").val();
            var houseNumber = $("input[name='custom_attributes[house_number]']").val();
            var houseNumberAddition = $("input[name='custom_attributes[house_number_addition]']").val();
            var postcode = $("input[name='postcode']").val();

            var localStorageData = checkoutData.getShippingAddressFromData();

            if (typeof streetName == "undefined" || streetName == '') {
                if (localStorageData) {
                    if (localStorageData.street_name !== undefined) streetName = localStorageData.street_name;
                    if ((typeof streetName == "undefined" || streetName == '') && localStorageData.street && localStorageData.street[0] !== '') {
                        streetName = localStorageData.street[0];
                    }
                    if (streetName == '') {
                        if (address.hasOwnProperty('customAttributes') && address.customAttributes !== undefined) {
                            if (address.customAttributes.hasOwnProperty('street_name') && address.customAttributes.street_name !== undefined) streetName = address.customAttributes.street_name.value;
                        }
                        else if (address.hasOwnProperty('custom_attributes') && address.custom_attributes !== undefined) {
                            if (address.custom_attributes.hasOwnProperty('street_name') && address.custom_attributes.street_name !== undefined) streetName = address.custom_attributes.street_name;
                        }
                    }
                }
            }
            if (typeof houseNumber == "undefined" || houseNumber == '') {
                if (localStorageData) {
                    if (localStorageData.house_number !== undefined) houseNumber = localStorageData.house_number;
                    if ((typeof houseNumber == "undefined" || houseNumber == '') && localStorageData.street && localStorageData.street[1] !== '') {
                        houseNumber = localStorageData.street[1];
                    }
                    if (houseNumber == '') {
                        if (address.hasOwnProperty('customAttributes') && address.customAttributes !== undefined) {
                            if (address.customAttributes.hasOwnProperty('house_number') && address.customAttributes.house_number !== undefined) houseNumber = address.customAttributes.house_number.value;
                        }
                        else if (address.hasOwnProperty('custom_attributes') && address.custom_attributes !== undefined) {
                            if (address.custom_attributes.hasOwnProperty('house_number') && address.custom_attributes.house_number !== undefined) houseNumber = address.custom_attributes.house_number;
                        }
                    }
                }
            }
            if (typeof houseNumberAddition == "undefined" || houseNumberAddition == '') {
                if (localStorageData) {
                    if (localStorageData.house_number_addition !== undefined) houseNumberAddition = localStorageData.house_number_addition;
                    if ((typeof houseNumberAddition == "undefined" || houseNumberAddition == '') && localStorageData.street && localStorageData.street[2] !== '') {
                        houseNumberAddition = localStorageData.street[2];
                    }
                    if (houseNumberAddition == '') {
                        if (address.hasOwnProperty('customAttributes') && address.customAttributes !== undefined) {
                            if (address.customAttributes.hasOwnProperty('house_number_addition') && address.customAttributes.house_number_addition !== undefined) houseNumberAddition = address.customAttributes.house_number_addition.value;
                        }
                        else if (address.hasOwnProperty('custom_attributes') && address.custom_attributes !== undefined) {
                            if (address.custom_attributes.hasOwnProperty('house_number_addition') && address.custom_attributes.house_number_addition !== undefined) houseNumberAddition = address.custom_attributes.house_number_addition;
                        }
                    }
                }
            }
            if (typeof postcode == "undefined" || postcode == '') {
                if (localStorageData) {
                    if (localStorageData.postcode !== undefined) postcode = localStorageData.postcode;
                    if ((typeof postcode == "undefined" || postcode == '') && localStorageData.postcode !== '') {
                        postcode = localStorageData.postcode;
                    }
                }
            }

            if (address.customAttributes === undefined) {
                address.customAttributes = {};
            }

            address.customAttributes.street_name = streetName;
            address.customAttributes.house_number = houseNumber;
            address.customAttributes.house_number_addition = houseNumberAddition;
            address.postcode = postcode;
            address.street = new Array();
            address.street[0] = streetName;
            address.street[1] = houseNumber;
            if (houseNumberAddition != '') {
                address.street[2] = houseNumberAddition;
            }

            return originalAction(address);
        });

        return defaultProcessor;
    };
});
