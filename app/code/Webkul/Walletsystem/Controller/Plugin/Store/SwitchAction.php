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

namespace Webkul\Walletsystem\Controller\Plugin\Store;

use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Webkul Walletsystem Class
 */
class SwitchAction
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cartModel;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var StoreCookieManagerInterface
     */
    private $storeCookieManager;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * Initialize dependencies
     *
     * @param \Webkul\Walletsystem\Helper\Data           $helper
     * @param \Magento\Checkout\Model\Session            $checkoutSession
     * @param \Magento\Checkout\Model\Cart               $checkoutCartModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Request\Http        $request
     * @param QuoteFactory                               $quoteFactory
     * @param StoreCookieManagerInterface                $storeCookieManager
     * @param StoreRepositoryInterface                   $storeRepository
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $checkoutCartModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        QuoteFactory $quoteFactory,
        StoreCookieManagerInterface $storeCookieManager,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->cartModel = $checkoutCartModel;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->quoteFactory = $quoteFactory;
        $this->storeCookieManager = $storeCookieManager;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Around Execute
     *
     * @param \Magento\Store\Controller\Store\SwitchAction $subject
     * @param \Closure $proceed
     * @return array
     */
    public function aroundExecute(
        \Magento\Store\Controller\Store\SwitchAction $subject,
        \Closure $proceed
    ) {
        $currentActiveStore = $this->storeManager->getStore();
        $previousStorecode = $this->storeCookieManager->getStoreCodeFromCookie();
        if ($previousStorecode==null) {
            $defaultStoreView = $this->storeManager->getDefaultStoreView();
            $previousStorecode = $defaultStoreView->getCode();
        }
        $previousStore = $this->storeRepository->getActiveStoreByCode($previousStorecode);
        $previousCurrency = $previousStore->getDefaultCurrencyCode();
        $currenctCurrency = $currentActiveStore->getCurrentCurrencyCode();

        $result = $proceed();

        if ($previousCurrency != $currenctCurrency) {
            $this->updateWalletAmountInCart($previousCurrency, $currenctCurrency);
        }
        return $result;
    }

    /**
     * Wallet system changes start
     *
     * @param string $previousCurrency
     * @param string $currenctCurrency
     */
    private function updateWalletAmountInCart($previousCurrency, $currenctCurrency)
    {
        $this->checkoutSession->unsWalletDiscount();
        $walletProductId = $this->helper->getWalletProductId();
        $currencySymbol = $this->helper->getCurrencySymbol(
            $this->storeManager->getStore()->getCurrentCurrencyCode()
        );
        $quote = '';
        if ($this->checkoutSession->getQuoteId()) {
            $quoteId = $this->checkoutSession->getQuoteId();
            $quote = $this->quoteFactory->create()
                ->load($quoteId);
        }
        if ($quote!='') {
            $cartData = $quote->getAllVisibleItems();
            if (!empty($cartData)) {
                foreach ($cartData as $cart) {
                    if ($cart->getProductId() == $walletProductId) {
                        $minimumAmount = $this->helper->getMinimumAmount();
                        $maximumAmount = $this->helper->getMaximumAmount();
                        if ($minimumAmount > $maximumAmount) {
                            $temp = $maximumAmount;
                            $maximumAmount = $minimumAmount;
                            $minimumAmount = $temp;
                        }
                        $finalPrice = $this->helper->getwkconvertCurrency(
                            $previousCurrency,
                            $currenctCurrency,
                            $cart->getCustomPrice()
                        );
                        $finalminimumAmount = $this->helper->getwkconvertCurrency(
                            $previousCurrency,
                            $currenctCurrency,
                            $minimumAmount
                        );
                        $finalmaximumAmount = $this->helper->getwkconvertCurrency(
                            $previousCurrency,
                            $currenctCurrency,
                            $maximumAmount
                        );
                        if ($finalPrice > $finalmaximumAmount) {
                            $finalPrice = $finalmaximumAmount;
                        } elseif ($finalPrice < $finalminimumAmount) {
                            $finalPrice = $finalminimumAmount;
                        }
                        $this->wkCartSave($cart, $finalPrice);
                    }
                }
            }
            $this->cartModel->save();
        }
    }

    /**
     * Save cart
     *
     * @param object $cart
     * @param int $finalPrice
     */
    private function wkCartSave($cart, $finalPrice)
    {
        $cart->setPrice($finalPrice);
        $cart->setCustomPrice($finalPrice);
        $cart->setOriginalCustomPrice($finalPrice);
        $cart->setRowTotal($finalPrice);
        $cart->save();
    }
}
