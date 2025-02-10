/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/mage',
    'Magento_Catalog/product/view/validation',
    'Bss_ConfigurableMatrixView/js/catalog-add-to-cart'
], function ($) {
    'use strict';

    $.widget('mage.productValidate', {
        options: {
            bindSubmit: false,
            radioCheckboxClosest: '.nested'
        },

        /**
         * Uses Magento's validation widget for the form object.
         * @private
         */
        _create: function () {
            var bindSubmit = this.options.bindSubmit;

            this.element.validation({
                radioCheckboxClosest: this.options.radioCheckboxClosest,

                /**
                 * Uses catalogAddToCart widget as submit handler.
                 * @param {Object} form
                 * @returns {Boolean}
                 */
                submitHandler: function (form) {
                    var jqForm = $(form).bssConfigurableAddToCart({
                        bindSubmit: bindSubmit
                    });

                    jqForm.bssConfigurableAddToCart('submitForm', jqForm);

                    return false;
                }
            });
        }
    });

    return $.mage.productValidate;
});
