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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'get_product_alert'
], function ($, getProductAlert) {
    "use strict";

    return async function (config, element) {
        $(element).parents(".product-item-info").find('.actions-primary').append(element);
        $(element).parent().find('.stock.unavailable').parent().append(element);
        $(element).parents(".product-item-info").find('.stock.unavailable').parent().append(element);
        $(element).parents(".product-item-info").find('.action.tocart').css('display','none');
        $(element).parents(".product.info").find('.actions-primary').append(element);
        $(element).parents(".product.info").find('.action.tocart').css('display','none');

        //Add btn stop notify to category page, search page.
        var productAlert = await getProductAlert(config);
        var stopConfig = productAlert && productAlert['product']
            ? productAlert['product'][config.product_id]
            : undefined;
        if (stopConfig) {
            var btnTextStop = productAlert['btn_text_stop'];
            $(element).attr('href', stopConfig.url_cancel);
            $(element).attr('title', btnTextStop);
            $(element).children('span').html(btnTextStop);
        }
    }
});
