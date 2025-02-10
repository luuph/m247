<?php
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
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\OrderDeliveryDate\Block\Adminhtml\Order\Creditmemo;

use Magento\Sales\Block\Adminhtml\Order\Creditmemo;

/**
 * Class TimeSlotPrice
 *
 * @package Bss\OrderDeliveryDate\Block\Adminhtml\Order\Creditmemo
 */
class TimeSlotPrice extends Creditmemo\Totals
{
    /**
     * Initialize creditmemo totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $this->addTotal(
            new \Magento\Framework\DataObject(
                [
                    'code' => 'delivery_time_slot_price',
                    'value' => $this->getSource()->getDeliveryTimeSlotPrice(),
                    'base_value' => $this->getSource()->getBaseDeliveryTimeSlotPrice(),
                    'label' => __('Delivery Time Slot Price'),
                ]
            )
        );
        return $this;
    }
}
