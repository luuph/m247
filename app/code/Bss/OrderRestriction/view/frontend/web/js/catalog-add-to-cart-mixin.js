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
 * @package    Bss_OrderRestriction
 * @author     Extension Team
 * @copyright  Copyright (c) 2021-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    var changeAddToCartButtonTextAdded = {
        options: {
            addToCartButtonTextAddedFailed: $t('Not allowed'),
            fallbackAddedText: null
        },

        /**
         * Invoke super initialize and module initialize logic
         *
         * @private
         */
        _create: function () {
            this.fallbackAddedText = this.addToCartButtonTextAdded;
            this._bindCheckTheRestrictionAddToCartRes();
            this._super();
        },

        /**
         * EN: Bind to listen the add to cart event for check if this action be restrict
         *
         * @private
         */
        _bindCheckTheRestrictionAddToCartRes: function () {
            $(document).on('ajax:addToCart', function (e, data) {
                if (data.response && data.response['bss_is_restricted'] === true) {
                    this.options.addToCartButtonTextAdded = this.options.addToCartButtonTextAddedFailed;
                } else {
                    this.options.addToCartButtonTextAdded = this.options.fallbackAddedText;
                }
            }.bind(this));
        }
    };

    return function (targetWidget) {
        $.widget('mage.catalogAddToCart', targetWidget, changeAddToCartButtonTextAdded);

        return $.mage.catalogAddToCart;
    };
});
