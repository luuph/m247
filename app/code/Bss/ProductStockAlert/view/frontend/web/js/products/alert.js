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
    'jquery'
], function ($) {
    "use strict";

    return async function (config) {
        /* Get all product alert by customer session */
        if (window.allProductAlert === undefined) {
            await $.ajax({
                type: 'GET',
                dataType: 'json',
                url: config.url_all_product_alert,
                success: function(response) {
                    window.allProductAlert = response;
                },
                complete: function (response) {
                    window.allProductAlert = window.allProductAlert ?? [];
                }
            });
        }

        return window.allProductAlert;
    }
});
