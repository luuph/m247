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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'underscore',
    'domReady!'
], function ($, customerData, _) {
    'use strict';

    $.widget('bss.coapQty', {
        options: {
            itemIdSelector: '#product_addtocart_form [name="item"]',
            productIdSelector: '#product_addtocart_form [name="product"]'
        },

        _create: function () {
            var $widget = this;
            $widget._getCoapOptionQty();
        },

        _getCoapOptionQty: function () {
            var $widget = this,
                product,
                cartData = customerData.get('cart')(),
                itemId = $($widget.options.itemIdSelector).val(),
                productId = $($widget.options.productIdSelector).val();
            if (!(cartData && cartData.items && cartData.items.length)) {
                return;
            }
            product = _.find(cartData.items, function (item) {
                if (item['item_id'] === itemId) {
                    return item['product_id'] === productId ||
                        item['item_id'] === productId;
                }
            });

            if (!product) {
                return;
            }
            $widget._updateCoapQty(product);
        },

        _updateCoapQty: function (product) {
            var optionId,
                qtyOption;
            $.each(product.options, function (key, val) {
                optionId = val.option_id;
                qtyOption = val.option_qty;
                $('#bss_option_qty_' + optionId).val(qtyOption);
            });
        }
    });

    return $.bss.coapQty;
});
