define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'mage/translate',
        'jquery'
    ],
    function (Component, quote, priceUtils, totals, $t, $) {
        "use strict";
        var walletconfig = window.checkoutConfig.payment.walletsystem;
        
        return Component.extend({
            defaults: {
                notCalculatedMessage: $t('Not yet calculated'),
                na: priceUtils.formatPrice(0,quote.getBasePriceFormat()),
                template: 'Webkul_Walletsystem/checkout/summary/wallet-system'
                
            },
            totals: quote.getTotals(),
            isDisplayed: function () {
                
                return this.totals() && this.isFullMode() && null != quote.shippingMethod();
            },
            isWalletAmountEnable: function () {
                return null != quote.paymentMethod() && quote.paymentMethod().method == 'walletsystem';
            },
            getValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment("wallet_amount").value;
                    if(totals.getSegment("wallet_amount").value){                        
                        $('.walletsystem').show();
                    } else {
                        $('.walletsystem').hide();                       
                    }
                }
                return this.getFormattedPrice(price);
            }
         });
    }
);