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
class CheckoutMultiShippingCreateOrder implements ObserverInterface
{
    /**
     * @var \Webkul\Walletsystem\Helper\Multishipping
     */
    protected $multihelper;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quote;

    /**
     * Constructor
     *
     * @param \Webkul\Walletsystem\Helper\Multishipping $multihelper
     * @param \Magento\Quote\Model\QuoteFactory $quote
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Multishipping $multihelper,
        \Magento\Quote\Model\QuoteFactory $quote
    ) {
        $this->multihelper = $multihelper;
        $this->quote = $quote;
    }

    /**
     * Walletsystem event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderData = $this->multihelper->getOrderData();
        $checkoutSession = $this->multihelper->getCheckoutSession();
        $order = $observer->getOrder();
        $quote = $observer->getOrder()->getQuote();
        $grandTotal = $order->getGrandTotal();
        $walletAmount = ($grandTotal / $orderData['orderTotal'] ) * $orderData['used_amount'];

        if ($walletAmount < 0) {
            $walletAmount = (-1) * $walletAmount;
        }

        $myValue = ['flag' => 1];
        $checkoutSession->setWalletDiscount($myValue);
        $quote->setWalletAmount($walletAmount);
        $quote->setBaseWalletAmount($walletAmount);
        $order->setWalletAmount($walletAmount);
        $order->setBaseWalletAmount($walletAmount);
        $order->setGrandTotal($grandTotal-$walletAmount);
    }
}
