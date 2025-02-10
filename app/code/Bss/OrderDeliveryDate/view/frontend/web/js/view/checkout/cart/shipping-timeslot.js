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
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Bss_OrderDeliveryDate/js/model/delivery-form-data'
], function(Component, priceUtilities, quote, totals, deliveryFormData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Bss_OrderDeliveryDate/checkout/cart/shipping-timeslot'
        },

        hasTimeSlot: function () {
            var timeSlotTitle;
            timeSlotTitle = this.getTimeSlotName();
            return window.checkoutConfig.bss_delivery_has_timeslot && timeSlotTitle !== '';
        },
        getValue: function() {
            var deliveryData = deliveryFormData.getSelectedDeliveryData(),
                price = 0;

            _.each(deliveryData, function (delivery) {
                if (undefined !== delivery.time_slot_price) {
                    price = delivery.time_slot_price;
                }
            });
            return this.getFormattedPrice(price);
        },
        getTimeSlotName: function () {
            var deliveryData = deliveryFormData.getSelectedDeliveryData(),
                timeSlotName = '';

            _.each(deliveryData, function (delivery) {
                if (undefined !== delivery.time_slot_name) {
                    timeSlotName = delivery.time_slot_name;
                }
            });
            return timeSlotName;
        }
    })
});
