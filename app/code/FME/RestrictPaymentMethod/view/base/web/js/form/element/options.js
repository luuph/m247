define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function ($, _, uiRegistry, select, modal) {
    'use strict';

    return select.extend({

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            if (value == '1') {
                $('div[data-index="sub_categories"]').css("display", "block");
                $('div[data-index="copy_sub_rules"]').css("display", "block");
                $('div[data-index="copy_sub_related"]').css("display", "block");
            } else {
                $('div[data-index="sub_categories"]').css("display", "none");
                $('div[data-index="copy_sub_rules"]').css("display", "none");
                $('div[data-index="copy_sub_related"]').css("display", "none");
            }
            return this._super();
        },
    });
    $(document).ready(function () {
        $('div[data-index="sub_categories"]').css("display", "none");
        $('div[data-index="copy_sub_rules"]').css("display", "none");
        $('div[data-index="copy_sub_related"]').css("display", "none");
    });
});