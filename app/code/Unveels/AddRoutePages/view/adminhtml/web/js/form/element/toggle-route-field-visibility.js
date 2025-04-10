define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, uiRegistry, select) {
    'use strict';

    return select.extend({
        initialize: function () {
            console.log("Initializing toggleRouteFieldVisibility component.");
            this._super();

            // Set initial visibility based on the current value
            this.toggleRouteFieldVisibility(this.value());

            return this;
        },

        onUpdate: function (value) {
            console.log("onUpdate called with value:", value);
            this.toggleRouteFieldVisibility(value);
            return this._super();
        },

        toggleRouteFieldVisibility: function (value) {
            console.log("toggleRouteFieldVisibility called with value:", value);

            // Use uiRegistry to find the route and pro_cat_id fields
            var fieldRoute = uiRegistry.get('index = route');
            var fieldProCatId = uiRegistry.get('index = pro_cat_id');
            console.log("Retrieved fieldRoute:", fieldRoute);
            console.log("Retrieved fieldProCatId:", fieldProCatId);

            // Ensure the fields are found and have the 'visible' and 'validation' functions
            if (fieldRoute && typeof fieldRoute.visible === 'function' && fieldProCatId && typeof fieldProCatId.visible === 'function') {
                if (value === 'page') { 
                    console.log("Showing route field and hiding pro_cat_id field.");
                    fieldRoute.visible(true); // Show the route field
                    fieldProCatId.visible(false); // Hide the pro_cat_id field

                    // Remove required-entry validation rule
                    fieldProCatId.validation['required-entry'] = false;
                } else {
                    console.log("Hiding route field and showing pro_cat_id field.");
                    fieldRoute.visible(false); // Hide the route field
                    fieldProCatId.visible(true); // Show the pro_cat_id field

                    // Reapply required-entry validation rule
                    fieldProCatId.validation['required-entry'] = true;
                }
            } else {
                console.error("Could not find route or pro_cat_id field or fields do not have visible method.");
            }
        }
    });
});
