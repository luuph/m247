/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_CurrencyFormatter
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
 var config = {
    config: {
        mixins: {
            // Add mixin for abstract total in the checkout summary
            'Magento_Checkout/js/view/summary/abstract-total': {
                'Mageplaza_CurrencyFormatter/js/view/summary/abstract-total': true
            },
            // Add mixin for price-box to handle configurable product prices
            'Magento_Catalog/js/price-box': {
                'Mageplaza_CurrencyFormatter/js/price-box': true
            }
        }
    }
};
