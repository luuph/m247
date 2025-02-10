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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'underscore',
    'mage/template',
    'priceUtils',
    'jquery/ui'
], function ($, _, mageTemplate) {
    'use strict';

    $.widget('bss.coabsStaticOption', {
        _create: function () {
            var $widget = this;
            $widget.element.on("click", ".admin__field-option", function() {
                var inputElement = $(this).find('.product-custom-option');
                var val = inputElement.val();
                if (inputElement.attr('type') == 'checkbox') {
                    if (inputElement.prop("checked")) {
                        $widget.element.find('.tier-option-value-'+val).show();
                    } else {
                        $widget.element.find('.tier-option-value-'+val).hide();
                    }
                } else if (inputElement.attr('type') == 'radio') {
                    $widget.element.find('.tier-hidden').hide();
                    $widget.element.find('.tier-option-value-'+val).show();
                }
            });
            $widget.element.find('select.product-custom-option:not(.multiselect)').change(function () {
                var val = $(this).val();
                $widget.element.find('.tier-hidden').hide();
                $widget.element.find('.tier-option-value-'+val).show();
            });
            $widget.element.find('.product-custom-option.multiselect').change(function () {
                var values = [];
                $(this).each(function () {
                    values.push($(this).val());
                });
                $widget.element.find('.tier-hidden').hide();
                $.each(values[0],function (index, val) {
                    $widget.element.find('.tier-option-value-'+val).show();
                });
            });
        }
    });
    return $.bss.coabsStaticOption;
});
