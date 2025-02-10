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
 * @copyright  Copyright (c) 2019-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    "jquery",
    'mage/template',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader',
    window.sliderBss,
    "jquery/ui",
    'mage/mage',
    'mage/validation'
], function ($, mageTemplate, getPaymentInformationAction, totals, fullScreenLoader) {
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
                var dataForm = $('#bss-reward-point-form');
                dataForm.validation();
                if ($(dataForm).valid()) {
                    $widget.sendAjax($(this.rewardPointValue).val())
                }
                return false;
            }, this));

            var slider = $( "#slider-point" ).slider({
                value: $('#bss-reward-point-value').val(),
                min: 0,
                max: $('#bss-reward-point-value').attr('max'),
                create: function() {
                    $widget.updateRangeinner($('#bss-reward-point-value').val());
                },
                slide: function( event, ui ) {
                    $('#bss-reward-point-value').val(parseInt(ui.value));
                    $widget.updateRangeinner(ui.value);
                },
                stop: function( event, ui ) {
                    $widget.updateRangeinner(ui.value);
                    $widget.sendAjax(ui.value);
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
        },

        sendAjax: function (spend_point) {
            $('.bss-reward-point').find('.message').remove();
            var url = $('#bss-reward-point-form').attr('action'),
                source = this.options.template,
                template = mageTemplate(source),
                html_mess = '';
            $.ajax({
                url: url,
                data: {spend_reward_point:spend_point},
                type: 'post',
                dataType: 'json',
                beforeSend: function() {
                    $('.bss-reward-point .loading').show();
                    fullScreenLoader.startLoader();
                },
                success: function(res) {
                    /* @deprecated use location.reload() */
                    // var deferred = $.Deferred();
                    // totals.isLoading(true);
                    // getPaymentInformationAction(deferred);
                    // $.when(deferred).done(function () {
                    //     fullScreenLoader.stopLoader();
                    //     totals.isLoading(false);
                    // });

                    if (res.pointLeft) {
                        $('.bss-reward-point .balance-price-label span:last-child').text(res.pointLeft);
                    }

                    if (res.spend_point) {
                        $('#bss-reward-point-value').val(parseInt(res.spend_point)).trigger('input');
                    }

                    html_mess = template({
                        data: {
                            status_message: res.status_message,
                            content_message: res.message
                        }
                    });
                    $('.bss-reward-point .content').prepend(html_mess);
                    $('.bss-reward-point .loading').hide();

                    location.reload();
                },
                complete : function(res){
                    setTimeout(function(){
                        $('.bss-reward-point').find('.message').fadeOut( "slow" );
                    }, 4000)
                },
                error : function(res) {
                    $('.bss-reward-point .loading').hide();
                }
            });
        }
    });

    return $.bss.rewardpoint;
});
