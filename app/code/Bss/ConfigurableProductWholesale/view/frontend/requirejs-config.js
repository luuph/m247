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
 * @package   Bss_ConfigurableProductWholesale
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

var config = {
    map: {
        '*': {
            'wholesale-renderer': 'Bss_ConfigurableProductWholesale/js/swatch-renderer',
            'priceBox' : 'Bss_ConfigurableProductWholesale/js/price-box'
        }
    },

    config: {
        mixins: {
            "Magento_ConfigurableProduct/js/configurable" : {
                "Bss_ConfigurableProductWholesale/js/configurable": true
            }
        }
    },
};
