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

use \Magento\Framework\App\Config\ScopeConfigInterface;
use Webkul\Walletsystem\Model\Walletcreditrules;

/**
 * Webkul Walletsystem Creditrules Class
 */
class PriceType extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $retrunArray = [
            Walletcreditrules::WALLET_CREDIT_CONFIG_AMOUNT_TYPE_FIXED => __('Fixed'),
            Walletcreditrules::WALLET_CREDIT_CONFIG_AMOUNT_TYPE_PERCENT => __('Percent')
        ];
        return $retrunArray;
    }
}
