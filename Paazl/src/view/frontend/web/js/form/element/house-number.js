/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract'
], function ($, uiRegistry, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            customName: '${ $.parentName }.${ $.index }',
            imports: {
                update: '${ $.parentName }.country_id:value'
            },
            selectorsToEnable: ['[name=street_name]', '[name=city]']
        },

        /**
         * @param {String} value
         */
        update: function (value) {
            if (!value) {
                return;
            }
            if (value != 'NL') {
                $.each(this.selectorsToEnable, function (key, selector) {
                    $(selector).prop('disabled', false);
                    $(selector).trigger('change');
                });
            }
        }
    });
});
