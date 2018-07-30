/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
var config = {
    config: {
        mixins: {
            // 'Magento_Checkout/js/model/shipping-rate-processor/new-address': {
            //     'Paazl_Shipping/js/model/shipping-rate-processor/new-address-mixin': true
            // }
            'Paazl_Shipping/js/model/shipping-rate-processor/new-address': {
                'Paazl_Shipping/js/model/shipping-rate-processor/new-address-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Paazl_Shipping/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Paazl_Shipping/js/view/shipping-mixin': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'Paazl_Shipping/js/view/billing-address-mixin': true
            }
        }
    },
    map: {
        '*': {
            fileElement: 'Paazl_Shipping/file-element',
            'Magento_Checkout/js/model/shipping-rate-processor/new-address':
                'Paazl_Shipping/js/model/shipping-rate-processor/new-address',
            'Magento_Checkout/template/billing-address/details.html':
                'Paazl_Shipping/template/billing-address/details.html',
            'Magento_Checkout/template/shipping-address/address-renderer/default.html':
                'Paazl_Shipping/template/shipping-address/address-renderer/default.html',
            'Magento_Checkout/template/shipping-information/address-renderer/default.html':
                'Paazl_Shipping/template/shipping-information/address-renderer/default.html'
        }
    }
};