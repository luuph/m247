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
            template: 'Bss_OneStepCheckout/summary/item/details/message-error'
        },
        displayArea: 'item_message_error',
        quoteMessages: quoteMessages,

        /**
         * @param {Object} item
         * @return {null}
         */
        getMessage: function (item) {
            var saleableQtyArr = window.checkoutConfig.saleableQty;
            var isManageStockArr = window.checkoutConfig.isManageStock;
            var self = this;
        
            var isScroll = false;
            if (saleableQtyArr !== null && saleableQtyArr !== undefined) {
                $.each(saleableQtyArr, function (index, saleableQty) {
                    if (saleableQty < 0) {
                        saleableQty = 0;
                    }
        
                    if (isManageStockArr[index] && saleableQty < item['qty']) {
                        self.quoteMessages[index] = $t("Not enough item for sale");
        
                        setTimeout(function () {
                            var $messageElement = $('.message.error.focus');

                        
                            if ($messageElement.length && !isScroll) {
                                $messageElement[0].scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                                isScroll = true;
                            }
                        }, 100);
                    } else {
                        self.quoteMessages[index] = undefined;
                    }
                });
            }
        
            if (this.quoteMessages[item['item_id']]) {
                return this.quoteMessages[item['item_id']];
            }
        
            return null;
        }
        
    });
});
