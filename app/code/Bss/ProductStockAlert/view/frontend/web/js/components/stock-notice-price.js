/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'mage/template',
    'Bss_ProductStockAlert/js/model/actions-price',
    'Bss_ProductStockAlert/js/helper/validation'
], function ($, mageTemplate, actionsModel, validation) {
    'use strict';

    $.widget('mage.bssProductPriceAlertProcessor', {
        options: {
            templateId: '#bss-price-notice-form',
            templateCancelId: '#bss-price-notice-cancel-form'
        },
        /**
         * @private
         */
        _create: function () {
            this._super();
            this._bindComponent();
            this._bindDocumentEvents();
        },
        /**
         * Bind widget
         * @private
         */
        _bindComponent: function () {
            actionsModel.setFormDataActionUrl(this.options.formDataActionUrl);
            actionsModel.requestFormData(this);
        },
        /**
         * Catch all events used
         * @private
         */
        _bindDocumentEvents: function () {
            // Bind event validate email before submit
            $(document).on('click', '.add-notice-email', function (e) {
                e.preventDefault();
                var resultValidate = validation.isEmailValid(this);
                if (!resultValidate) {
                    return;
                }
                var formElem = $(this).closest('form.stockalert-form'),
                    tempFormElem = formElem.clone(),
                    uniqId = Math.random().toString(36).substring(7);
                tempFormElem.attr('id', uniqId);
                tempFormElem.appendTo($('body')).submit();
                tempFormElem.detach();
            });
        },
    });

    return $.mage.bssProductPriceAlertProcessor;
});
