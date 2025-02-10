/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/sidebar',
    'jquery/jquery.cookie',
    'domReady!',
], function ($, Component, quote, stepNavigator, sidebarModel) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mageants_StorePickup/shipping-information'
        },

        /**
         * @return {Boolean}
         */
        isVisible: function () {
            return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
        },

        /**
         * @return {String}
         */
        getShippingMethodTitle: function () {
            var shippingMethod = quote.shippingMethod();

            return shippingMethod ? shippingMethod['carrier_title'] + ' - ' + shippingMethod['method_title'] : '';
        },

        /**
         * Back step.
         */
        back: function () {
            sidebarModel.hide();
            stepNavigator.navigateTo('shipping');
        },

        /**
         * Back to shipping method.
         */
        backToShippingMethod: function () {
            sidebarModel.hide();
            stepNavigator.navigateTo('shipping', 'opc-shipping_method');
        },
         /*
          * @return {String}
         */
        getPickup: function () {
            if(quote.shippingMethod().carrier_code == 'storepickup' && parseInt($.cookie('pickupStoreVal')) === 1)
            {
                if($.cookie('pickupAddress')){
                    var obj = JSON.parse($.cookie('pickupAddress'));
                    var str = '<div>'+obj.firstname+ " " + obj.lastname + "</div> <div>"+obj.street[0]+"</div><div>"+obj.city+" "+obj.region+"</div>";
                    return str;
                }
            }
            return false;
        }
    });
});
