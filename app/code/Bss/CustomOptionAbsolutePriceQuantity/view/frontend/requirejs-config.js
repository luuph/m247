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
var config = {
    map: {
        '*': {
            coapSubtotal: 'Bss_CustomOptionAbsolutePriceQuantity/js/coap-subtotal',
            priceOptions: 'Bss_CustomOptionAbsolutePriceQuantity/js/custom-price-options',
            priceOptionDate: 'Bss_CustomOptionAbsolutePriceQuantity/js/custom-price-option-date',
            coapTip: 'Bss_CustomOptionAbsolutePriceQuantity/js/coap-static-tip'
        }
    },
    config: {
        mixins: {
            'Bss_GroupedProductOption/js/price-options': {
                'Bss_CustomOptionAbsolutePriceQuantity/js/bss-price-options-mixin': true
            }
        }
    }
};
