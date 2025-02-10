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
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Bss_RewardPoint/js/action/set_spendpoint',
        'jquery/ui',
        window.checkoutConfig.bssSlider
    ],
    function ($, ko, Component, setSpendpointAction) {
        'use strict';

        var data = window.checkoutConfig,
            isPointSlider = ko.observable(data.pointSlider),
            pointLeft = ko.observable(data.point_left),
            rateHtml = ko.observable(data.rateHtml),
            cancel = ko.observable(0),
            point = ko.observable(null),
            isDisplayed = ko.observable(data.display);
        if (data.spend_point) {
            point(parseInt(data.spend_point));
        }

        return Component.extend({
            defaults: {
                template: 'Bss_RewardPoint/checkout/payment/spend_point'
            },
            point : point,
            pointLeft: pointLeft,
            isPointSlider: isPointSlider,
            rateHtml: rateHtml,

            apply: function () {
                if (this.validate()) {
                    setSpendpointAction(point, pointLeft);
                }
            },

            cancel: function () {
                if (this.validate()) {
                    setSpendpointAction(cancel, pointLeft);
                }
            },

            validate: function () {
                var form = '#bss-reward-point-form';
                return $(form).validation() && $(form).validation('isValid');
            },

            updateRangeinner: function (point_use) {
                var max_point = window.checkoutConfig.point_balance;
                var rangeinner = parseFloat(100*parseInt(point_use)/parseInt(max_point)).toFixed(5);
                $('.rangeinner').css("width", rangeinner + '%');
            },

            isDisplayed: function () {
                var $this = this;
                if (data.display) {
                    if (data.pointSlider) {
                        var slider = $('#slider-point').slider({
                            value: data.spend_point,
                            min: 0,
                            max: data.point_balance,
                            create: function() {
                                $this.updateRangeinner(data.spend_point);
                            },
                            slide: function( event, ui ) {
                                $('#bss-reward-point-value').val(parseInt(ui.value));
                                $this.updateRangeinner(ui.value);
                            },
                            stop: function( event, ui ) {
                                point(parseInt(ui.value));
                                $this.updateRangeinner(ui.value);
                                setSpendpointAction(point, pointLeft);
                            }
                        });

                        $('#bss-reward-point-value').attr('max', data.point_balance);
                        $('body').on('input', '#bss-reward-point-value', function() {
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
                            $this.updateRangeinner(value);
                        });
                    }
                    return true;
                }
            }
        });
    }
);
