/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

define(
    [],
    function () {
        'use strict';
        return {
            getRules: function() {
                return {
                    'postcode': {
                        'required': true
                    },
                    'country_id': {
                        'required': true
                    },
                    'street_name': {
                        'required': false,
                        'custom_attribute': true,
                    },
                    'house_number': {
                        'required': true,
                        'custom_attribute': true,
                    },
                    'house_number_addition': {
                        'custom_attribute': true,
                    }
                };
            }
        };
    }
);
