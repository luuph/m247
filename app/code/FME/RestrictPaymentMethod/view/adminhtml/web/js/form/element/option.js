define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';

    return select.extend({

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            var field1 = uiRegistry.get('index = country');
            if (value == 0) {
                field1.show();
            } else {
                field1.hide();
            }

            var field2 = uiRegistry.get('index = region_id');
            if (value == 1) {
                field2.show();
            } else {
                field2.hide();
            }

            return this._super();
        },
    });
});