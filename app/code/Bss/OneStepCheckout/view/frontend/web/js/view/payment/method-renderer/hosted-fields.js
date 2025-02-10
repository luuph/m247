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
 * @category  BSS
 * @package   Bss_OneStepCheckout
 * @author    Extension Team
 * @copyright Copyright (c) 2024-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'PayPal_Braintree/js/view/payment/method-renderer/cc-form',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/payment/additional-validators',
], function ($, Component, fullScreenLoader, additionalValidators) {
    'use strict';
    return function (Component) {
        return Component.extend({
            placeOrderClick: function () {
                if (this.validateFormFields() && additionalValidators.validate()) {
                    this.placeOrder();
                } else {
                    fullScreenLoader.stopLoader();
                }
            }
        });
    };
});
