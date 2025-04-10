define([
    'jquery',
    'uiComponent',
    'mage/url', 
    'mage/storage', 
    'Magento_Customer/js/customer-data'
], function ($, Component, urlBuilder, storage, customerData) {
    'use strict';

    return Component.extend({
        isReloading: false,  // Flag to avoid multiple reloads

        initialize: function () {
            this._super();
            console.log("Unveels_RestrictDiscounts: restrict-discount.js");

            this.bindButtonClicks();
            this.fetchCartTotalsAndManageDiscountBlocks();
        },

        fetchCartTotalsAndManageDiscountBlocks: function (callback) {
            var customer = customerData.get('customer')();
            var self = this;

            if (self.isReloading) {
                return;
            }

            self.isReloading = true;

            customerData.reload(['cart'], true).done(function () {
                var cartTotals = customerData.get('cart')();

                var serviceUrl;
                if (customer && customer.firstname) {
                    serviceUrl = urlBuilder.build('rest/V1/carts/mine/totals');
                } else {
                    var cartId = localStorage.getItem('guest-cart-id');
                    if (!cartId) {
                        self.isReloading = false;
                        return;
                    }
                    serviceUrl = urlBuilder.build('rest/V1/guest-carts/' + cartId + '/totals');
                }

                storage.get(serviceUrl)
                    .done(function (totalsData) {
                        self.manageBlocksBasedOnDiscounts(totalsData);
                        self.isReloading = false;

                        if (callback) callback();
                    })
                    .fail(function () {
                        self.isReloading = false;
                        self.showAllBlocks();
                    });
            }).fail(function () {
                self.isReloading = false;
            });
        },

        manageBlocksBasedOnDiscounts: function (totalsData) {
            var couponApplied = totalsData.coupon_code ? true : false;
            var giftCardApplied = totalsData.total_segments.some(segment => segment.code === 'bss_giftcard');
            var rewardsDeductionApplied = totalsData.total_segments.some(segment => segment.code === 'rewards-spend-amount' && segment.value < 0);

            if (giftCardApplied) {
                this.hideBlocksExceptGift();
            } else if (couponApplied) {
                this.hideBlocksExceptCoupon();
            } else if (rewardsDeductionApplied) {
                this.hideBlocksExceptRewards();
            } else {
                this.showAllBlocks();
            }
        },

        hideBlocksExceptCoupon: function () {
            $('.bss-giftcard').hide();
            $('.rewards-block').hide();
            $('.discount-code').show();
        },

        hideBlocksExceptGift: function () {
            $('.bss-giftcard').show();
            $('.rewards-block').hide();
            $('.discount-code').hide();
        },

        hideBlocksExceptRewards: function () {
            $('.bss-giftcard').hide();
            $('.rewards-block').show();
            $('.discount-code').hide();
        },

        showAllBlocks: function () {
            $('.bss-giftcard').show();
            $('.rewards-block').show();
            $('.discount-code').show();
        },

        bindButtonClicks: function () {
            var self = this;

            $(document).on('click', '.action-apply', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });

            $(document).on('click', '.action-cancel', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });

            $(document).on('click', '.bss-giftcard-apply', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });

            $(document).on('click', '.bss-giftcard-remove', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });

            $(document).on('click', '.button.action[data-bind*="rewardsFormSubmit"]', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });

            $(document).on('change', '#points_all', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });

            $(document).on('click', '.button.action[data-bind*="rewardsFormSubmit"][data-bind*="true"]', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });
        }
    });
});
