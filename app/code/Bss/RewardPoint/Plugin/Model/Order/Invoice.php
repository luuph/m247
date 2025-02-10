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
namespace Bss\RewardPoint\Plugin\Model\Order;

class Invoice
{
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;
    /**
     * Invoice constructor.
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     */
    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Register
     *
     * @param \Magento\Sales\Model\Order\Invoice $subject
     * @return \Magento\Sales\Model\Order\Invoice
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeRegister(\Magento\Sales\Model\Order\Invoice $subject)
    {
        $order = $subject->getOrder();
        if (!$subject->getId() && !$subject->getBaseRwpAmount()) {
            $quote = $this->quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
            $baseBalance = (float)$quote->getBaseRwpAmount();
            $balance = (float)$quote->getRwpAmount();
            if ($baseBalance > 0 && $balance > 0) {
                if ($order->getInvoiceCollection()->getSize() > 0) {
                    $invoiceBaseRwpAmount = 0;
                    $invoiceRwpAmount = 0;
                    foreach ($order->getInvoiceCollection() as $invoiceOrder) {
                        $invoiceBaseRwpAmount += $invoiceOrder->getBaseRwpAmount();
                        $invoiceRwpAmount += $invoiceOrder->getRwpAmount();
                    }
                    $baseBalance -= $invoiceRwpAmount;
                    $balance -= $invoiceBaseRwpAmount;
                }
                $baseGrandTotal = $subject->getBaseGrandTotal();
                $grandTotal = $subject->getGrandTotal();
                if ($baseBalance >= $baseGrandTotal) {
                    $baseBalanceUsedLeft = $baseGrandTotal;
                    $balanceUsedLeft = $grandTotal;
                    $subject->setBaseGrandTotal(0);
                    $subject->setGrandTotal(0);
                } else {
                    $baseBalanceUsedLeft = $baseBalance;
                    $balanceUsedLeft = $balance;
                    $subject->setBaseGrandTotal($baseGrandTotal - $baseBalanceUsedLeft);
                    $subject->setGrandTotal($grandTotal - $balanceUsedLeft);
                }
                $subject->setRwpAmount($balanceUsedLeft);
                $subject->setBaseRwpAmount($baseBalanceUsedLeft);
            }
        }
        return $subject;
    }
}
