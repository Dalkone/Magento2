/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'mage/mage'
], function ($) {
    'use strict';

    return function (config) {
        var dataForm = $('#form-validate');

        if (config.hasUserDefinedAttributes) {
            dataForm = dataForm.mage('fileElement', {});
        }
        dataForm.mage('validation', config);

        if (config.disableAutoComplete) {
            dataForm.find('input:text').attr('autocomplete', 'off');
        }
    };
});
