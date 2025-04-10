define([
    'jquery',
    'ko',
    'Magento_Checkout/js/model/quote'
], function ($, ko, quote) {
    'use strict';

    return function (Component) {
        return Component.extend({
            initialize: function () {
                this._super();
                console.log("✅ Billing Address Mixin Loaded");
                console.log(this.isAddressSameAsShipping);

                if (ko.isObservable(this.isAddressSameAsShipping)) {
                    this.isAddressSameAsShipping(true);
                    console.log("✅ Billing address checkbox checked by default");
                } else {
                    console.error("❌ isAddressSameAsShipping is NOT an observable!");
                }

                // Ensure billing section exists before hiding/showing it
                var checkExist = setInterval(function () {
                    if ($('.payment-method-billing-address').length > 0) {
                        console.log("🔎 Billing address section detected!");
                        clearInterval(checkExist);
                        updateBillingVisibility();
                    }
                }, 500);

                // Subscribe to cart changes and update the billing visibility dynamically
                quote.getItems().subscribe(function () {
                    updateBillingVisibility();
                });

                function updateBillingVisibility() {
                    const cartItems = quote.getItems();
                    console.log("🛒 Updated Cart Items:", cartItems);

                    const hasGiftCard = cartItems.some(
                        (item) => item.product_type === "bss_giftcard"
                    );

                    if ($('.payment-method-billing-address').length > 0) {
                        if (hasGiftCard) {
                            $('.payment-method-billing-address').show();
                            console.log("🎁 Gift card found: Showing billing address checkbox");
                        } else {
                            $('.payment-method-billing-address').hide();
                            console.log("✅ No gift card: Hiding billing address section");
                        }
                    }
                }
            }
        });
    };
});
