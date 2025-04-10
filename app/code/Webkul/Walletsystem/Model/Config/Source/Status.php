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

use Magento\Framework\Data\OptionSourceInterface;
use Webkul\Walletsystem\Model\Walletcreditrules;

/**
 * Class Status
 * Set enable/disable walletsystem
 */
class Status implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    /**
     * Get option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            Walletcreditrules::WALLET_CREDIT_RULE_STATUS_ENABLE => __('Enabled'),
            Walletcreditrules::WALLET_CREDIT_RULE_STATUS_DISABLE => __('Disabled')
        ];
    }
}
