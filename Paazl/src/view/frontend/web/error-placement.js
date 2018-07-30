/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
 define([
    'jquery',
    'mageUtils'
], function ($, utils) {
    'use strict';

    return function (config) {
        var dobElement = null,
            errorClass = null;

        if (config.hasUserDefinedAttributes || config.isDobEnabled) {
            return utils.extend(config, {

                errorPlacement: function (error, element) {
                    if (element.prop('id').search('full') !== -1) {
                        dobElement = $(element).parents('.customer-dob');
                        errorClass = error.prop('class');
                        error.insertAfter(element.parent());
                        dobElement.find('.validate-custom').addClass(errorClass)
                            .after('<div class="' + errorClass + '"></div>');
                    } else {
                        error.insertAfter(element);
                    }
                }
            });
        }
    };
});
