/*browser:true*/
/*global define*/
define(
        [
            'jquery',
            'Magento_Checkout/js/view/payment/default',
            'mage/url'
        ],
        function (
                $,
                Component,
                url
                ) {
            'use strict';
            var self;

            var urlCode = 'myfatoorah_payment';
            var checkoutConfig = window.checkoutConfig.payment.myfatoorah_payment;

            var mfData = 'pm=myfatoorah';
            var mfError = checkoutConfig.mfError;

            return Component.extend({
                redirectAfterPlaceOrder: false,
                defaults: {
                    template: 'MyFatoorah_Gateway/payment/redirect'
                },
                initialize: function () {
                    this._super();
                    self = this;
                },
                initObservable: function () {
                    this._super().observe([
                        'gateways',
                        'transactionResult'
                    ]);

                    return this;

                },
                getCode: function () {
                    return urlCode;
                },
                getData: function () {
                    return {
                        'method': this.item.method,
                        'additional_data': {
                            'gateways': this.gateways(),
                            'transaction_result': this.transactionResult()
                        }
                    };
                },
                validate: function () {
                    return true;
                },
                getTitle: function () {
                    return checkoutConfig.title;
                },
                afterPlaceOrder: function () {
                    window.location.replace(url.build(urlCode + '/checkout/index?' + mfData));
                },
                PlaceOrderMyFatoorah: function () {
                    if (!self.placeOrder()) {
                        $('body').loader('hide');
                    }
                    return;
                },
                placeOrderForm: function () {
                    if (mfError) {
                        return false;
                    }
                    $('body').loader('show');
                    return self.PlaceOrderMyFatoorah();

                }
            });
        }
);