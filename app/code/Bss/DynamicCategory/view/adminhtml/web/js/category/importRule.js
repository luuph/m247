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

    $.widget('bss.importRule', {
        _create: function () {
            let url = this.options.url;
            $('.bss-dynamic-category-import-button').click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                let data = $(':input').serializeArray();
                data.forEach((data) => {
                    var name = data.name;
                    var value =  data.value;
                    if (name === 'import_conditions_field' && value === '') {
                        data.value = 'default';
                    }
                })
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    cache: true,
                    showLoader: true,
                    success: function (res) {
                        let ruleBlock = $('.bss-dynamic-category-rule-tree').first();
                        ruleBlock.empty();
                        ruleBlock.html(res);
                        ruleBlock.trigger('contentUpdated')
                    }
                });
            });
        }
    });

    return $.bss.importRule;
});
