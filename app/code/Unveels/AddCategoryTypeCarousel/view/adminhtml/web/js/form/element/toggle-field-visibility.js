define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, uiRegistry, select) {
    'use strict';

    return select.extend({
        initialize: function () {
            console.log("Initializing toggleFieldVisibility component.");
            this._super();

            // Set initial visibility based on the current value
            this.toggleFieldVisibility(this.value());

            return this;
        },

        onUpdate: function (value) {
            console.log("onUpdate called with value:", value);
            this.toggleFieldVisibility(value);
            return this._super();
        },

        toggleFieldVisibility: function (value) {
            console.log("toggleFieldVisibility called with value:", value);

            // Use uiRegistry.async to wait for the field to be fully available
            uiRegistry.async('index = image_size')(function (fieldImageSize) {
                console.log("Retrieved fieldImageSize:", fieldImageSize);
                if (fieldImageSize && typeof fieldImageSize.visible === 'function') {
                    if (value === '1') { // Adjust the condition based on your needs
                        console.log("Showing image_size field.");
                        fieldImageSize.visible(true); // Show the field
                    } else {
                        console.log("Hiding image_size field.");
                        fieldImageSize.visible(false); // Hide the field
                    }
                }
            });

            uiRegistry.async('index = categories')(function (fieldCategory) {
                console.log("Retrieved fieldCategory:", fieldCategory);
                if (fieldCategory && typeof fieldCategory.visible === 'function') {
                    if (value === '3') { // Show fields when value is 3
                        console.log("Showing category field.");
                        fieldCategory.visible(true);
                    } else {
                        console.log("Hiding category field.");
                        fieldCategory.visible(false);
                    }
                }
            });

            uiRegistry.async('index = number_of_products')(function (fieldNumberOfProducts) {
                console.log("Retrieved fieldNumberOfProducts:", fieldNumberOfProducts);
                if (fieldNumberOfProducts && typeof fieldNumberOfProducts.visible === 'function') {
                    if (value === '3') { // Show fields when value is 3
                        console.log("Showing number_of_products field.");
                        fieldNumberOfProducts.visible(true);
                    } else {
                        console.log("Hiding number_of_products field.");
                        fieldNumberOfProducts.visible(false);
                    }
                }
            });
        }
    });
});
