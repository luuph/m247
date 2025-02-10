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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'mage/translate'
    ],
    function ($, Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Bss_RewardPoint/checkout/summary/spend_point'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,

            isDisplayed: function () {
                return this.isFullMode() && this.getPureValue() !== 0;
            },

            getValue: function () {
                var spend_point = parseInt($('[name="spend_reward_point"]').val());
                var html_spend_point = ' (' + spend_point + ' ' + $.mage.__('point') + ')';
                if (spend_point > 1) {
                    html_spend_point = ' (' + spend_point + ' ' + $.mage.__('points') + ')';
                }
                return this.getFormattedPrice(this.getPureValue()) + html_spend_point;
            },
            
            getPureValue: function () {
                var price = 0,
                segment;
                
                if (this.totals) {
                    segment = totals.getSegment('spend_point');

                    if (segment) {
                        price = segment.value;
                    }
                }
                return price;
            }
        });
    }
);