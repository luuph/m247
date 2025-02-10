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
 * @package   Bss_FixCustomerAttributes
 * @author    Extension Team
 * @copyright Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

var config = {
    map: {
        '*': {
            'Magento_Checkout/template/shipping-address/address-renderer/default':
                'Bss_FixCustomerAttributes/template/shipping-address/address-renderer/default',
            'Magento_Checkout/template/billing-address/details':
                'Bss_FixCustomerAttributes/template/billing-address/details',
            'Magento_Checkout/template/shipping-information/address-renderer/default':
                'Bss_FixCustomerAttributes/template/shipping-information/address-renderer/default'
        }
    }
};
