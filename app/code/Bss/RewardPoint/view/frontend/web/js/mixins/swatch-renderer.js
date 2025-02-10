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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'mage/url',
    'underscore',
    'jquery/ui',
    'jquery/jquery.parsequery'
], function ($, urlBuilder, _) {
    'use strict';
    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            firstMessage: '',

            _create: function () {
                this._super();
                this.firstMessage = $('.child-configurable span').text();
            },

            _OnClick: function ($this, $widget) {
                $widget._super($this, $widget);

                this._UpdateRewardPointMessage();
            },

            _OnChange: function ($this, $widget) {
                $widget._super($this, $widget);

                this._UpdateRewardPointMessage();
            },

            _UpdateRewardPointMessage: function () {
                var $widget = this,
                    index = '',
                    childProductData = this.options.jsonConfig.childProduct;
                $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                    index += $(this).attr('option-selected') + '_';
                });
                if (index == '') {
                    $('.child-configurable span').text(this.firstMessage);
                    $('.child-configurable').show();
                }
                if (!childProductData['child'].hasOwnProperty(index)) {
                    return false;
                }
                $('.child-configurable span').text(childProductData['child'][index]['reward_point']);
                if (childProductData['child'][index]['reward_point'].length > 0) {
                    $('.child-configurable').show();
                } else {
                    $('.child-configurable').hide();
                }
            },
        });

        return $.mage.SwatchRenderer;
    }
});
