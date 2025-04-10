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
class Quotesubmitbefore implements ObserverInterface
{
    /**
     * Walletsystem event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        $walletAmount =  $observer->getEvent()->getQuote()->getShippingAddress()->getWalletAmount();
        $baseWalletAmount =  $observer->getEvent()->getQuote()->getShippingAddress()->getBaseWalletAmount();
        if ($walletAmount==null || $walletAmount==0) {
            $walletAmount =  $observer->getEvent()->getQuote()->getBillingAddress()->getWalletAmount();
            $baseWalletAmount =  $observer->getEvent()->getQuote()->getBillingAddress()->getBaseWalletAmount();
        }
        $order = $observer->getEvent()->getOrder();
        $order->setWalletAmount(-$walletAmount);
        $order->setBaseWalletAmount(-$baseWalletAmount);
        return $this;
    }
}
