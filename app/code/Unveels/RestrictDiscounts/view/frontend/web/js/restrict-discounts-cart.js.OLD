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
            console.log("Unveels_RestrictDiscounts: restrict-discount-cart.js");

            // Bind button events once
            this.bindButtonClicks();

            // Initial fetch of cart totals and discount block management
            this.fetchCartTotalsAndManageDiscountBlocks();
        },

        fetchCartTotalsAndManageDiscountBlocks: function (callback) {
            var customer = customerData.get('customer')();
            var self = this;

            // Check if a reload is already in progress
            if (self.isReloading) {
                return;
            }

            self.isReloading = true; // Set reloading flag

            // Reload cart data once
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

                // Fetch cart totals
                storage.get(serviceUrl)
                    .done(function (totalsData) {
                        self.manageBlocksBasedOnDiscounts(totalsData);

                        // Reset the reloading flag after fetch
                        self.isReloading = false;

                        if (callback) callback();
                    })
                    .fail(function () {
                        self.isReloading = false;
                        self.showAllBlocks();
                    });
            }).fail(function () {
                self.isReloading = false;  // Reset in case of failure
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
            $('#block-bss-giftcard').hide();
            $('#block-rewards-form').hide();
            $('#block-discount').show();
        },

        hideBlocksExceptGift: function () {
            $('#block-bss-giftcard').show();
            $('#block-rewards-form').hide();
            $('#block-discount').hide();
        },

        hideBlocksExceptRewards: function () {
            $('#block-bss-giftcard').hide();
            $('#block-rewards-form').show();
            $('#block-discount').hide();
        },

        showAllBlocks: function () {
            $('#block-bss-giftcard').show();
            $('#block-rewards-form').show();
            $('#block-discount').show();
        },

        bindButtonClicks: function () {
            var self = this;

            // Apply coupon button
            $(document).on('click', '.action-apply', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });

            // Cancel coupon button
            $(document).on('click', '.action-cancel', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });

            // Apply gift card button
            $(document).on('click', '.bss-giftcard-apply', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });

            // Remove gift card button
            $(document).on('click', '.bss-giftcard-remove', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });

            // Apply reward points button
            $(document).on('click', '.button.action[data-bind*="rewardsFormSubmit"]', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });

            // Max points checkbox
            $(document).on('change', '#points_all', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });

            // Cancel reward points
            $(document).on('click', '.button.action[data-bind*="rewardsFormSubmit"][data-bind*="true"]', function () {
                self.fetchCartTotalsAndManageDiscountBlocks();
            });
        }
    });
});
