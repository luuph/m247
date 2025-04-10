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

namespace Webkul\Walletsystem\Plugin\Paypal\Model\Api;

/**
 * Class DiscountConfigureProcess
 *
 * Removes discount block when wallet amount product is in cart.
 */
class Nvp
{

   /**
    * Constructor
    *
    * @param \Webkul\Walletsystem\Helper\Data $helper
    * @param \Magento\Checkout\Model\Session $checkoutSession
    */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * After Get Title function
     *
     * @param \Magento\Paypal\Model\Api\Nvp $subject
     * @param $string $methodName
     * @param array $request
     * @return array
     */
    public function beforeCall(
        \Magento\Paypal\Model\Api\Nvp $subject,
        $methodName,
        array $request
    ) {
        if ($this->checkoutSession->getWalletDiscount() && !$this->checkoutSession->getPaypalAmountForWallet()) {
            if (isset($request["AMT"]) && isset($request["ITEMAMT"])) {
                $walletData = $this->checkoutSession->getWalletDiscount();
                $baseCurrencyCode = $this->helper->getBaseCurrencyCode();
                $currrentCurrencyCode = $this->helper->getCurrentCurrencyCode();
                $baseWalletAmt = $this->helper->getwkconvertCurrency(
                    $currrentCurrencyCode,
                    $baseCurrencyCode,
                    $walletData["amount"]
                );
                $amount = $request["AMT"] - round($baseWalletAmt, 2);
                if ($amount > 0.00) {
                    $request["AMT"] -= round($baseWalletAmt, 2);
                }
                $this->checkoutSession->setPaypalAmountForWallet(true);
            }
        }
        return [$methodName,$request];
    }
}
