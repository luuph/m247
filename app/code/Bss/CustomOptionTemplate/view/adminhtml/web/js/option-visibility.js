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
        },
        initConfig: function () {
            this._super();
            this.multiselectCustomerId = 'multiselectCustomer-' + this.uid;
            this.multiselectStored = 'multiselectStore-' + this.uid;
            this.eventCheck(this);
            return this;
        },
        eventCheck: function ($widget) {
            $("body").on("click", '.option-visible.template-div-modal:has(#'+$widget.multiselectCustomerId+') .action-close,' +
                '.option-visible.template-div-modal:has(#'+$widget.multiselectCustomerId+') .action-primary', function(){
                var elementModal = $('.option-visible.template-div-modal:has(#'+$widget.multiselectCustomerId+')');
                var data = {};
                data.visible_for_group_customer = '';
                data.visible_for_store_view = '';
                console.log($widget);
                if (elementModal.find('.visible-option-customer').val()) {
                    data.visible_for_group_customer = elementModal.find('.visible-option-customer').val().toString();
                }
                if (elementModal.find('.visible-option-store').val()) {
                    data.visible_for_store_view = elementModal.find('.visible-option-store').val().toString();
                }
                $widget.value(JSON.stringify(data));
                $(this).parents('.option-visible.template-div-modal').find('aside').removeClass('_show');
            });
        },
        showModal: function () {
            $('.option-visible.template-div-modal:has(#'+this.multiselectCustomerId+') .product_form_product_form_store_title_modal').addClass('_show');
            if (this.value()) {
                var data = this.value();
                data = $.parseJSON(data);
                var elementModal = $('.option-visible.template-div-modal:has(#'+this.multiselectCustomerId+')');
                var visibleCustomer = data.visible_for_group_customer !== '' ? data.visible_for_group_customer.split(',') : '';
                var visibleStore = data.visible_for_store_view !== '' ? data.visible_for_store_view.split(',') : '';
                elementModal.find('.visible-option-customer').val(visibleCustomer);
                elementModal.find('.visible-option-store').val(visibleStore);
                if (elementModal.parents('.scheduled-changes-modal-slide').length) {
                    $('.scheduled-changes-modal-slide').find('.product_form_product_form_store_title_modal._show').css(
                        {'top': $('.scheduled-changes-modal-slide .modal-inner-wrap').scrollTop() +'px', 'bottom': 'auto'}
                    );
                }
            }
        },
    });
});
