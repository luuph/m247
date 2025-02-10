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
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderDeliveryDate\Model\Total\Quote;

class TimeSlotPrice extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Set Order Totals
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this|TimeSlotPrice
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $timeSlotPrice = (float) $quote->getTimeSlotPrice();
        $baseTimeSlotPrice = (float) $quote->getBaseTimeSlotPrice();
        $total->addTotalAmount($this->getCode(), $timeSlotPrice);
        $total->addBaseTotalAmount($this->getCode(), $baseTimeSlotPrice);

        return $this;
    }
}
