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

namespace Webkul\Walletsystem\Model\Transaction\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Webkul\Walletsystem\Model\Wallettransaction;

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
     * Get Option Array function
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            Wallettransaction::WALLET_TRANS_STATE_PENDING => __('Pending'),
            Wallettransaction::WALLET_TRANS_STATE_APPROVE => __('Approved'),
            Wallettransaction::WALLET_TRANS_STATE_CANCEL => __('Cancelled')
        ];
    }
}
