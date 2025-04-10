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

namespace Webkul\Walletsystem\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul Walletsystem Class
 */
class SalesOrderPayInvoice implements ObserverInterface
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $helper;

    /**
     * Initialize dependencies
     *
     * @param \Webkul\Walletsystem\Helper\Data $helper
     */
    public function __construct(\Webkul\Walletsystem\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Invoice save after
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $invoice = $event->getInvoice();
        $order = $invoice->getOrder();
        $orderTotalPaid = $order->getTotalPaid();
        $orderBaseTotalPaid = $order->getBaseTotalPaid();
        $walletAmount = (-1) *  ($invoice->getWalletAmount());
        $baseWalletAmount = (-1) * ($this->helper->baseCurrencyAmount($invoice->getWalletAmount()));
        $invoice->setWalletAmount($walletAmount);
        $order->setBaseTotalPaid($orderBaseTotalPaid + $baseWalletAmount);
        $order->setTotalPaid($orderTotalPaid + $walletAmount);
        $invoice->setGrandTotal($order->getGrandTotal());

        return $this;
    }
}
