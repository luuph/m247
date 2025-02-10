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
    'mage/translate'
], function ($, _) {
    'use strict';

    $.widget('bss.coapTip', {
        _create: function () {
            var $widget = this;
            $widget.updateTip($widget);
        },
        updateTip: function ($widget) {
            $.each($widget.options.priceTypeData, function (index, value) {
                $widget.element.find('input[value="' + value + '"]').parent('.field.choice').find('label')
                .append($.mage.__('(absolute price)'));
            });
        }
    });
    return $.bss.coapTip;
});
