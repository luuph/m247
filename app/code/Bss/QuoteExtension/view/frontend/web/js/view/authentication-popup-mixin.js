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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define(function () {
    'use strict';

    return function (target) {
        return target.extend({

            /**
             * Sets modal on given HTML element with on demand initialization.
             */
            setModalElement: function (element) {
                if (window.location.pathname.includes("quoteextension")) {
                    this.createPopup(element);
                }
            }
        });
    }
});
