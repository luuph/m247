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
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'ko',
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/lib/validation/validator',
    'mage/translate'
], function (ko, $, _, abstract, validator) {
    'use strict';

    return abstract.extend({
        defaults: {
            multiselectValue: ko.observableArray(),
        },
        initConfig: function () {
            this._super();
            this.multiselectId = 'multiselect-' + this.uid;
            return this;
        },
        multiselectValue: function () {
            return this.value().split(',');
        },
        updateExclude: function () {
            if ($('#' + this.multiselectId).val()) {
                this.value($('#' + this.multiselectId).val().join(','));
            } else {
                this.value("");
            }
            $('.check_template_data_input').each(function() {
                var multiElementId = 'multiselect-'+$('[name="product[tenplates_excluded]"]').attr('id');
                var multiExclude = $('#'+ multiElementId).val();
                if (!multiExclude) {
                    multiExclude = [];
                }
                var values = $.map($('#'+ multiElementId + ' option') ,function(option) {
                    return option.value;
                });
                var optionData = {template_option_id : 0};
                if ($(this).val()) {
                    optionData = JSON.parse(jQuery(this).val());
                }
                var element = $(this).closest('td').find('.admin__collapsible-block-wrapper > .fieldset-wrapper-title +.admin__collapsible-content');
                var optionButtonDelete = $(this).closest('td').closest('td').find('.action-delete');
                if (element.length > 0 && $.inArray(optionData.template_option_id, values) !== -1 &&  $.inArray(optionData.template_option_id, multiExclude) === -1) {
                    element.css({"pointer-events": "none", "opacity": 0.5});
                    optionButtonDelete.css({"pointer-events": "none", "opacity": 0.5});
                } else {
                    element.css({"pointer-events": "unset", "opacity": 1});
                    optionButtonDelete.css({"pointer-events": "unset", "opacity": 1});
                }
            });
        },
    });
});
