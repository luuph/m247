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
 * @package    Bss_DynamicCategory
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery'
], function ($) {
    "use strict";

    $.widget('bss.productPreview', {
        _create: function () {
            let url = this.options.url;
            $('.bss-dynamic-category-button').click(function (e) {
                $('#catalog_category_products').css('display','none');
                e.preventDefault();
                e.stopPropagation();
                let data = $(':input').serializeArray();
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    cache: true,
                    showLoader: true,
                    success: function (res) {
                        let productListEl = $('.bss-dynamic-category-list');
                        productListEl.html(res);
                        productListEl.trigger('contentUpdated')
                    }
                });
            });
        }
    });

    return $.bss.productPreview;
});
