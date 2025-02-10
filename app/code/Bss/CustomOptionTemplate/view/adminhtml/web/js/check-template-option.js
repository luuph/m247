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
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/abstract',
    'mage/translate'
], function ($, _, abstract) {
    'use strict';

    return abstract.extend({
        defaults: {
            template_exclude_id: '',
        },
        initConfig: function () {
            this._super();
            this.template_exclude_id = 'multiselect-'+$('[name="product[tenplates_excluded]"]').attr('id');
            return this;
        },
        checkTemplate: function () {
            var multiElementId = 'multiselect-'+$('[name="product[tenplates_excluded]"]').attr('id');
            var multiExclude = $('#'+ multiElementId).val();
            if (!multiExclude) {
                multiExclude = [];
            }

            var values = $.map($('#'+ multiElementId + ' option') ,function(option) {
                return option.value;
            });
            var optionData = {template_option_id : 0};
            if (this.value()) {
                optionData = JSON.parse(this.value());
            }
            var element = $('label[for="'+this.uid+'"]').closest('td').find('.admin__collapsible-block-wrapper > .fieldset-wrapper-title +.admin__collapsible-content');
            var optionButtonDelete = $('label[for="'+this.uid+'"]').closest('td').find('.action-delete');
            if (element.length > 0 && $.inArray(optionData.template_option_id, values) !== -1 && $.inArray(optionData.template_option_id, multiExclude) === -1) {
                element.css({"pointer-events": "none", "opacity": 0.5});
                optionButtonDelete.css({"pointer-events": "none", "opacity": 0.5});
            }
            return 0;
        },
    });
});
