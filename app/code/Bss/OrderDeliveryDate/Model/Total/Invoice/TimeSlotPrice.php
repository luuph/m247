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
namespace Bss\OrderDeliveryDate\Model\Total\Invoice;

use Bss\OrderDeliveryDate\Helper\Data;

class TimeSlotPrice extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param array $data
     */
    public function __construct(
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($data);
    }

    /**
     * Set Invoice Totals
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this|TimeSlotPrice
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        parent::collect($invoice);
        $timeSlotPrice = $invoice->getOrder()->getTimeSlotPrice();
        $baseTimeSlotPrice = $invoice->getOrder()->getBaseTimeSlotPrice();

        $invoice->setGrandTotal($invoice->getGrandTotal() + $timeSlotPrice);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTimeSlotPrice);

        return $this;
    }
}
