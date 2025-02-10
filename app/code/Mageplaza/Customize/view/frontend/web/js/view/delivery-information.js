/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DeliveryTime
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(
    [
        'jquery',
        'ko',
        'underscore',
        'uiComponent',
        'Mageplaza_DeliveryTime/js/model/mpdt-data',
        'Mageplaza_DeliveryTime/js/model/delivery-information',
        'Magento_Checkout/js/model/quote',
        'jquery/ui',
        'jquery/jquery-ui-timepicker-addon'
    ],
    function ($, ko, _, Component, mpDtData, deliveryInformation, quote) {
        'use strict';

        var cacheKeyDeliveryDate = 'deliveryDate',
            cacheKeyDeliveryTime = 'deliveryTime',
            cacheKeyHouseSecurityCode = 'houseSecurityCode',
            cacheKeyDeliveryComment = 'deliveryComment',
            dateFormat = window.checkoutConfig.mpDtConfig.deliveryDateFormat,
            daysOff = window.checkoutConfig.mpDtConfig.deliveryDaysOff || [],
            dateOff = [],
            cutoffTime = window.checkoutConfig.mpDtConfig.cutoffTime;
            if(!cutoffTime[1]) {
                cutoffTime[1] = 0;
            }

        function prepareSubscribeValue(object, cacheKey) {
            
            object(mpDtData.getData(cacheKey));
            object.subscribe(function (newValue) {
                mpDtData.setData(cacheKey, newValue);
            });
        }


        function formatDeliveryTime(time) {
            var from = time['from'][0] + 'h' + time['from'][1],
                to = time['to'][0] + 'h' + time['to'][1];
            return from + ' - ' + to;
        }

        return Component.extend({
            defaults: {
                template: 'Mageplaza_DeliveryTime/container/delivery-information'
            },
            deliveryDate: deliveryInformation().deliveryDate,
            deliveryTime: deliveryInformation().deliveryTime,
            houseSecurityCode: deliveryInformation().houseSecurityCode,
            deliveryComment: deliveryInformation().deliveryComment,
            deliveryTimeOptions: deliveryInformation().deliveryTimeOptions,
            isVisible: ko.observable(mpDtData.getData(cacheKeyDeliveryDate)),
            allowDisplay: ko.observable(false),

            initialize: function () {
                this._super();
                var self = this;

                dateOff = _.pluck(window.checkoutConfig.mpDtConfig.deliveryDateOff, 'date_off');
                ko.bindingHandlers.mpdatepicker = {
                    init: function (element) {
                        var options = {
                            minDate: 0,
                            showButtonPanel: false,
                            dateFormat: dateFormat,
                            showOn: 'both',
                            buttonText: '',
                            beforeShowDay: function (date) {
                                return self.isAvailable(date);
                            }
                        };
                        $(element).datepicker(options);
                    }
                };

                $.each(window.checkoutConfig.mpDtConfig.deliveryTime, function (index, item) {
                    self.deliveryTimeOptions.push(formatDeliveryTime(item));
                });

                prepareSubscribeValue(this.deliveryDate, cacheKeyDeliveryDate);
                prepareSubscribeValue(this.deliveryTime, cacheKeyDeliveryTime);
                prepareSubscribeValue(this.houseSecurityCode, cacheKeyHouseSecurityCode);
                prepareSubscribeValue(this.deliveryComment, cacheKeyDeliveryComment);

                this.isVisible = ko.computed(function () {
                    return !!self.deliveryDate();
                });
                quote.shippingAddress.subscribe(function (address) {	
                    var countries = checkoutConfig.mpDtConfig.allowCountries;	
                    if(countries == null){	
                        self.allowDisplay(true);	
                    }else{	
                        self.allowDisplay(_.contains(countries.split(","),address.countryId));	
                    }	
                });
                return this;
            },

            removeDeliveryDate: function () {
                if (mpDtData.getData(cacheKeyDeliveryDate) && mpDtData.getData(cacheKeyDeliveryDate) != null) {
                    this.deliveryDate('');
                }
            },

            isAvailable: function (date) {
                var currentDay = date.getDay();
                var currentDate = date.getDate();
                var currentMonth = date.getMonth() + 1;
                var currentYear = date.getFullYear();
                var dateToCheck = currentDate + '/' + currentMonth + '/' + currentYear;
                var isAvailableDay = daysOff.indexOf(currentDay) === -1;
                var isAvailableDate = $.inArray(dateToCheck, dateOff) === -1;

                if (cutoffTime == null) {
                    return [isAvailableDay && isAvailableDate];
                }

                var now = new Date();
                var timeCut = cutoffTime[0]*3600000 + cutoffTime[1]*60000;
                var timeNow = now.getHours()*3600000 + now.getMinutes()*60000;
                var isToday = date.getDate() === now.getDate() && date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear();

                if (isToday && timeNow < timeCut) {
                    return [isAvailableDay && isAvailableDate];
                }
                var nextDeliveryDay,
                n = 1;
                for (var i = 0; i < n; i++ ) {
                    var time = new Date();
                    time.setDate(now.getDate() + i);
                    var isValidDay = daysOff.indexOf(time.getDay()) === -1;
                    var Month = time.getMonth() + 1;
                    var formatMonth = String(Month).length > 1 ? Month : '0' + Month;
                    var dateCheck = ('0' + time.getDate()).slice(-2) + '/' + formatMonth + '/' + time.getFullYear();
                    var isvalidDate = $.inArray(dateCheck, dateOff) === -1;
                    if (isValidDay && isvalidDate) {
                        nextDeliveryDay = time;
                        break;
                    }
                    n++;
                }

                var isNextDelyveryDay = date.getDate() === nextDeliveryDay.getDate() && date.getMonth() === nextDeliveryDay.getMonth() && date.getFullYear() === nextDeliveryDay.getFullYear();
                if (isNextDelyveryDay && timeNow >= timeCut) {
                    return false;
                }

                return [isAvailableDay && isAvailableDate];
            }

        });
    }
);
