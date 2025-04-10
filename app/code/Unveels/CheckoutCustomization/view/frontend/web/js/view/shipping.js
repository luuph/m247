define([
    'jquery',
    'ko',
    'Magento_Checkout/js/model/quote'
], function ($, ko, quote) {
    'use strict';

    return function (target) {
        console.log("Mixin Loaded: Custom Shipping");

        return target.extend({
            defaults: {
                isAddressSame: ko.observable(true), // Set the default value
                isAddressSameVisible: ko.observable(false) // Hide the checkbox
            },

            initialize: function () {
                this._super();
                console.log("Shipping Component Initialized");

                // Ensure the checkbox is selected by default
                this.isAddressSame(true);

                // Hide the checkbox if needed
                var shouldHideCheckbox = true; // Change this condition based on your requirement
                if (shouldHideCheckbox) {
                    this.isAddressSameVisible(false);
                }
            }
        });
    };
});
