define(
    [
        'Webkul_Walletsystem/js/view/checkout/summary/wallet-system',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component,quote) {
        'use strict';

        return Component.extend({

            /**
             * @override
             */
            isDisplayed: function () {
                return !!quote.shippingMethod();
            }
        });
    }
);