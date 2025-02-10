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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;

class SpendPoint extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * Collect
     *
     * @param Invoice $invoice
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collect(Invoice $invoice)
    {
        parent::collect($invoice);
        $order = $invoice->getOrder();
        $spendBaseRwpAmount = $order->getBaseRwpAmount();
        $spendRwpAmount = $order->getRwpAmount();
        if (!$order->getId() || !$spendBaseRwpAmount) {
            return;
        }
        if (!$invoice->getId() && !empty($order->getInvoiceCollection()->getData())) {
            $invoiceBaseBssRwpAmount = 0;
            $invoiceBssRwpAmount = 0;
            foreach ($order->getInvoiceCollection() as $invoiceOrder) {
                $invoiceBaseBssRwpAmount += $invoiceOrder->getBaseRwpAmount();
                $invoiceBssRwpAmount += $invoiceOrder->getRwpAmount();
            }
            $spendBaseRwpAmount -= $invoiceBssRwpAmount;
            $spendRwpAmount -= $invoiceBaseBssRwpAmount;
        }
        $baseGrandTotal = $invoice->getBaseGrandTotal();
        $grandTotal = $invoice->getGrandTotal();
        if ($spendBaseRwpAmount >= $baseGrandTotal) {
            $baseRwpAmountUsedLeft = $baseGrandTotal;
            $rwpAmountUsedLeft = $grandTotal;
            $invoice->setBaseGrandTotal(0);
            $invoice->setGrandTotal(0);
        } else {
            $baseRwpAmountUsedLeft = $spendBaseRwpAmount;
            $rwpAmountUsedLeft = $spendRwpAmount;
            $invoice->setBaseGrandTotal($baseGrandTotal - $baseRwpAmountUsedLeft);
            $invoice->setGrandTotal($grandTotal - $rwpAmountUsedLeft);
        }
        $invoice->setRwpAmount($rwpAmountUsedLeft);
        $invoice->setBaseRwpAmount($baseRwpAmountUsedLeft);
    }
}
