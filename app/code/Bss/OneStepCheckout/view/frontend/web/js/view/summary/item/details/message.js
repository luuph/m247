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
 * @copyright Copyright (c) 2023-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'uiComponent',
    'jquery',
    'mage/translate'
], function (Component, $, $t) {
    'use strict';

    var quoteMessages = window.checkoutConfig.quoteMessages;

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details/message'
        },
        displayArea: 'item_message',
        quoteMessages: quoteMessages,

        /**
         * @param {Object} item
         * @return {null}
         */
        getMessage: function (item) {
            var saleableQtyArr = window.checkoutConfig.saleableQty;
            var isManageStockArr = window.checkoutConfig.isManageStock;
            var backordersArr = window.checkoutConfig.backorders;

            var self = this;
            if (saleableQtyArr !== null && saleableQtyArr !== undefined) {
                $.each(saleableQtyArr, function ( index, saleableQty ) {
                    if (saleableQty < 0) {
                        saleableQty = 0;
                    }

                    if (isManageStockArr[index] && backordersArr[index] == '2' // product: isManageStock and BACKORDERS_YES_NOTIFY
                        && saleableQty - item['qty'] < 0
                    ) {
                        let backOrderQty = item['qty'] - saleableQty;
                        self.quoteMessages[index] = $t("We don't have as many quantity as you requested, but we'll back order the remaining " + backOrderQty +".");
                    } else {
                        self.quoteMessages[index] = undefined;
                    }
                })
            }
            if (this.quoteMessages[item['item_id']]) {
                return this.quoteMessages[item['item_id']];
            }

            return null;
        }
    });
});
