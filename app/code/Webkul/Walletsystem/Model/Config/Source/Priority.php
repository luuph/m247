<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Model\Config\Source;

use Webkul\Walletsystem\Model\Walletcreditrules;

/**
 * Webkul Walletsystem Creditrules Class
 */
class Priority extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $retrunArray = [
            Walletcreditrules::WALLET_CREDIT_CONFIG_PRIORITY_PRODUCT_BASED => __('Product based'),
            Walletcreditrules::WALLET_CREDIT_CONFIG_PRIORITY_CART_BASED => __('Cart Based')
        ];
        return $retrunArray;
    }
}
