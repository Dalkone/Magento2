/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function(billingAction) {
        //if targetModule is a uiClass based object
        return billingAction.extend({
            updateAddress:function()
            {
                var addressData;
                var streetName = '';
                var houseNumber = '';
                var houseNumberAddition = '';

                addressData = this.source.get(this.dataScopePrefix);

                // fixup street
                if (addressData.hasOwnProperty('custom_attributes') && addressData.custom_attributes !== undefined) {
                    if (addressData.custom_attributes.hasOwnProperty('street_name') && addressData.custom_attributes.street_name !== undefined) streetName = addressData.custom_attributes.street_name;
                    if (addressData.custom_attributes.hasOwnProperty('house_number') && addressData.custom_attributes.house_number !== undefined) houseNumber = addressData.custom_attributes.house_number;
                    if (addressData.custom_attributes.hasOwnProperty('house_number_addition') && addressData.custom_attributes.house_number_addition !== undefined) houseNumberAddition = addressData.custom_attributes.house_number_addition;

                    addressData.street = new Array();
                    addressData.street[0] = streetName;
                    addressData.street[1] = houseNumber;
                    if (houseNumberAddition != '') {
                        addressData.street[2] = houseNumberAddition;
                    }
                }

                this.source.set(this.dataScopePrefix, addressData);

                var result = this._super(); //call parent method

                return result;
            }
        });
    };
});