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
namespace Bss\RewardPoint\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class SpendPoint extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * SpendPoint constructor.
     *
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($data);
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Collect
     *
     * @param Creditmemo $creditmemo
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collect(Creditmemo $creditmemo)
    {
        parent::collect($creditmemo);

        $order = $creditmemo->getOrder();
        if (!$order->getBaseRwpAmount()) {
            return $this;
        }

        $invoicebaseRwpAmount = $this->getCreditInvoice($order);
        $creditmemobaseRwpAmount = $this->getCreditCreditmemo($order);
        $baseBalance = $invoicebaseRwpAmount - $creditmemobaseRwpAmount;
        if ($baseBalance >= $creditmemo->getBaseGrandTotal()) {
            $baseBalanceUsedLeft = $creditmemo->getBaseGrandTotal();
            $balanceUsedLeft = $creditmemo->getGrandTotal();
            $creditmemo->setBaseGrandTotal(0)
                        ->setGrandTotal(0)
                        ->setAllowZeroGrandTotal(true);
        } else {
            $baseBalanceUsedLeft = $baseBalance;
            $balanceUsedLeft = $this->priceCurrency->convert($baseBalanceUsedLeft, $creditmemo->getStore());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseBalanceUsedLeft)
                        ->setGrandTotal($creditmemo->getGrandTotal() - $balanceUsedLeft);
        }
        $creditmemo->setBaseRwpAmount($baseBalanceUsedLeft)
            ->setRwpAmount($balanceUsedLeft);
        return $this;
    }

    /**
     * Get credit invoice
     *
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getCreditInvoice($order)
    {
        $invoicebaseRwpAmount = 0;
        if ($order->hasInvoices()) {
            foreach ($order->getInvoiceCollection() as $invoice) {
                $invoicebaseRwpAmount += $invoice->getBaseRwpAmount();
            }
        }
        return $invoicebaseRwpAmount;
    }

    /**
     * Get creditmemo
     *
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getCreditCreditmemo($order)
    {
        $creditmemobaseRwpAmount = 0;
        if ($order->hasCreditmemos()) {
            foreach ($order->getCreditmemosCollection() as $creditmemo) {
                $creditmemobaseRwpAmount += $creditmemo->getBaseRwpAmount();
            }
        }
        return $creditmemobaseRwpAmount;
    }
}
