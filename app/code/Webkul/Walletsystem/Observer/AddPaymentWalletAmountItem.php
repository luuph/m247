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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Webkul Walletsystem Add Payment Wallet Amount Item Class
 */
class AddPaymentWalletAmountItem implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutsession
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutsession,
        \Webkul\Walletsystem\Helper\Data $walletHelper
    ) {
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutsession;
        $this->helper = $walletHelper;
    }

    /**
     * Add wallet amount as custom item to payment cart totals.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Payment\Model\Cart $cart */
        $cart = $observer->getEvent()->getCart();

        $getSession = $this->checkoutSession->getWalletDiscount();
        if (is_array($getSession) && array_key_exists('amount', $getSession) && $getSession['amount']!=0) {
            $baseAmount = $this->helper->baseCurrencyAmount($getSession['amount']);
            $cart->addCustomItem(__('Wallet Amount'), 1, -1.00 * $baseAmount);
        }
    }
}
