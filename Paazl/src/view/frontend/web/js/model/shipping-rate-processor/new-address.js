/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

/**
 * @todo Temporary file until I find a cleaner way to alter the payload / set the extension attributes server side.
 */
define(
    [
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/error-processor',
        'jquery'
    ],
    function (resourceUrlManager, quote, storage, shippingService, rateRegistry, errorProcessor, $) {
        'use strict';

        return {
            /**
             * Get shipping rates for specified address.
             * @param {Object} address
             */
            getRates: function (address) {
                shippingService.isLoading(true);
                var cache = rateRegistry.get(address.getCacheKey()),
                    serviceUrl = resourceUrlManager.getUrlForEstimationShippingMethodsForNewAddress(quote),
                    payload = JSON.stringify({
                            address: {
                                'street': address.street,
                                'city': address.city,
                                'region_id': address.regionId,
                                'region': address.region,
                                'country_id': address.countryId,
                                'postcode': address.postcode,
                                'email': address.email,
                                'customer_id': address.customerId,
                                'firstname': address.firstname,
                                'lastname': address.lastname,
                                'middlename': address.middlename,
                                'prefix': address.prefix,
                                'suffix': address.suffix,
                                'vat_id': address.vatId,
                                'company': address.company,
                                'telephone': address.telephone,
                                'fax': address.fax,
                                'custom_attributes': address.customAttributes,
                                'extension_attributes': address.customAttributes,
                                'save_in_address_book': address.saveInAddressBook
                            }
                        }
                    );

                // We need house number to be able to get shipping options. Unless we are on cart page
                if (((address.customAttributes && address.customAttributes.house_number == '') || typeof address.customAttributes.house_number == 'undefined') && $('input[name="custom_attributes[house_number]"]').length > 0) {
                    shippingService.setShippingRates([]);
                    $(".table-checkout-shipping-method input[type=radio]").prop("disabled", false);
                    shippingService.isLoading(false);
                    return;
                }

                if (cache) {
                    shippingService.setShippingRates(cache);
                    shippingService.isLoading(false);
                    $(".table-checkout-shipping-method input[type=radio]").prop("disabled", false);
                } else {
                    storage.post(
                        serviceUrl, payload, false
                    ).done(
                        function (result) {
                            rateRegistry.set(address.getCacheKey(), result);
                            shippingService.setShippingRates(result);
                            $(".table-checkout-shipping-method input[type=radio]").prop("disabled", false);
                        }
                    ).fail(
                        function (response) {
                            shippingService.setShippingRates([]);
                            errorProcessor.process(response);
                            $(".table-checkout-shipping-method input[type=radio]").prop("disabled", false);
                        }
                    ).always(
                        function () {
                            shippingService.isLoading(false);
                        }
                    );
                }
            }
        };
    }
);
