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
            store_view : [],
        },
        initConfig: function () {
            this._super();
            this.multiselectCustomerId = 'multiselectCustomer-' + this.uid;
            this.multiselectStored = 'multiselectStore-' + this.uid;
            this.eventCheck(this);
            return this;
        },
        eventCheck: function ($widget) {
            jQuery("body").on("click", '.option-store-title-div.template-div-modal:has(#'+$widget.uid+') .add-store-title-button', function(){
                jQuery(this).parent().parent().find('aside').addClass('_show');
            });
            jQuery("body").on("click", '.option-store-title-div.template-div-modal:has(#'+$widget.uid+') .action-close', function(){
                jQuery(this).parents('.option-store-title-div.template-div-modal').find('aside').removeClass('_show');
            });
            jQuery("body").on("click", '.option-store-title-div.template-div-modal:has(#'+$widget.uid+') .action-primary', function(){
                jQuery(this).parents('.option-store-title-div.template-div-modal').find('aside').removeClass('_show');
            });
        },
        getStoreViewTitleInput: function (value) {
            return this.inputName+'['+value+']';
        },
        multiselectValue: function () {
            return this.value().split(',');
        }
    });
});
