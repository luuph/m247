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
    "jquery",
    'mage/template',
    "jquery/ui",
    'mage/mage',
    'mage/validation'
], function ($, mageTemplate) {
    "use strict";

    $.widget('bss.rewardpoint', {
        options: {
            bssRewardPointValue : '#bss-reward-point-value',
            bssRewardPointApply : 'button.action.bss-reward-point-apply',
            template:
                '<div class="messages">' + 
                '<div class="message-<%- data.status_message %> <%- data.status_message %> message">'+ 
                '<div><%- data.content_message %></div>' + 
                '</div></div>',
        },
        _create: function () {
            var $widget = this;
            this.rewardPointValue = $(this.options.bssRewardPointValue);

            $(this.options.bssRewardPointApply).on('click', $.proxy(function () {
                this.rewardPointValue.attr('data-validate', '{required:true}');
                $(this.element).validation().submit();
                return false;
            }, this));

            var slider = $( "#slider-point" ).slider({
                value: parseInt($('#bss-reward-point-value').val()),
                min: 0,
                max: parseInt($('#bss-reward-point-value').attr('max')),
                create: function() {
                    $widget.updateRangeinner($('#bss-reward-point-value').val());
                },
                slide: function( event, ui ) {
                    $('#bss-reward-point-value').val(parseInt(ui.value));
                    $widget.updateRangeinner(ui.value);
                },
                stop: function( event, ui ) {
                    $widget.updateRangeinner(ui.value);
                }
            });

            $(this.rewardPointValue).on( "input", function() {
                if (parseInt($(this).val()) > parseInt($(this).attr('max'))) {
                    $(this).val($(this).attr('max'));
                }
                if (parseInt($(this).val()) <= 0) {
                    $(this).val(0);
                }
                var value = parseInt($(this).val());
                value = value > 0 ? value : 0;
                slider.slider('value',value);
                slider.slider("value",slider.slider("value"));
                $widget.updateRangeinner(value);
            });
             $(this.rewardPointValue).trigger('input');
        },

        updateRangeinner: function (point_use) {
            var max_point = $('#bss-reward-point-value').attr('max');
            var rangeinner = parseFloat(100*parseInt(point_use)/parseInt(max_point)).toFixed(5);
            $('.rangeinner').css("width", rangeinner + '%');
        }
    });

    return $.bss.rewardpoint;
});
