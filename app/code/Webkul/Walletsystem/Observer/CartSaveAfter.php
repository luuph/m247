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
use Magento\Sales\Model\OrderFactory;
use Magento\Quote\Model\Quote\ItemFactory;

/**
 * Webkul Walletsystem Class
 */
class CartSaveAfter implements ObserverInterface
{
    /**
     * @var orderFactory
     */
    protected $orderFactory;

    /**
     * @var itemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * Initialize dependencies
     *
     * @param OrderFactory                                $orderFactory
     * @param ItemFactory                                 $itemFactory
     * @param \Webkul\Walletsystem\Helper\Data            $helper
     * @param \Magento\Framework\App\Request\Http         $request
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\ResponseInterface    $response
     */
    public function __construct(
        OrderFactory $orderFactory,
        ItemFactory $itemFactory,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->orderFactory = $orderFactory;
        $this->itemFactory = $itemFactory;
        $this->helper = $helper;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->response = $response;
    }

    /**
     * Cart save after observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $walletProductId = $this->helper->getWalletProductId();
        $event = $observer->getQuoteItem();
        $params = $this->request->getParams();
        $minimumAmount = $this->helper->getMinimumAmount();
        $maximumAmount = $this->helper->getMaximumAmount();
        $cartData = $this->checkoutSession->getQuote()->getAllVisibleItems();
        if ($minimumAmount > $maximumAmount) {
            $temp = $minimumAmount;
            $minimumAmount = $maximumAmount;
            $maximumAmount = $temp;
        }
        $currentCurrenyCode = $this->helper->getCurrentCurrencyCode();
        $baseCurrenyCode = $this->helper->getBaseCurrencyCode();

        $finalminimumAmount = $this->helper->getwkconvertCurrency(
            $baseCurrenyCode,
            $currentCurrenyCode,
            $minimumAmount
        );
        $finalmaximumAmount = $this->helper->getwkconvertCurrency(
            $baseCurrenyCode,
            $currentCurrenyCode,
            $maximumAmount
        );
        $itemId = '';
        $itemIds = '';
        $price = '';
        $flag = 0;
        $walletInCart = 0;
        $otherInCart = 0;
        $currencySymbol = $this->helper->getCurrencySymbol(
            $this->helper->getCurrentCurrencyCode()
        );
        $controllerAction = $this->request->getActionName();
        if ($controllerAction == 'reorder') {
            $orderId = $params['order_id'];
            $order = $this->orderFactory->create()->load($orderId);
            if (!empty($cartData)) {
                foreach ($cartData as $cart) {
                    if ($cart->getProduct()->getId() == $walletProductId) {
                        $itemIds = $cart->getItemId();
                        $price = $cart->getCustomPrice();
                        $walletInCart = 1;
                    } else {
                        $otherInCart = 1;
                        if ($walletInCart == 1) {
                            $itemIds = $cart->getItemId();
                            break;
                        }
                    }
                }
            }
            if ($otherInCart == 1 && $walletInCart == 1) {
                $this->messageManager->addNotice(
                    __(
                        'You can not add other products with wallet product, and vise versa.'
                    )
                );
                $quote = $this->itemFactory->create()->load($itemIds);
                $quote->delete();
            } else {
                $this->reorderWalletProductAdd($cartData, $itemIds, $price, $finalmaximumAmount, $finalminimumAmount);
            }
        } else {
            if (array_key_exists('product', $params)) {
                $this->singleProductAdded($cartData, $params, $finalmaximumAmount, $finalminimumAmount);
            }
        }
        // $this->checkoutSession->getQuote()->save();
    }

    /**
     * Update Wallet Product Item function
     *
     * @param object $cart
     * @param integer $itemId
     * @param integer $price
     * @param integer $finalmaximumAmount
     * @param integer $finalminimumAmount
     * @return integer $flag
     */
    public function updateWalletProductItem($cart, $itemId, $price, $finalmaximumAmount, $finalminimumAmount)
    {
        $flag = 0;
        $currencySymbol = $this->helper->getCurrencySymbol(
            $this->helper->getCurrentCurrencyCode()
        );
        if ($cart->getItemId() != $itemId) {
            $amount = $price + $cart->getCustomPrice();
            if ($amount > $finalmaximumAmount) {
                $amount = $finalmaximumAmount;
                $this->messageManager->addNotice(
                    __(
                        'You can not add more than %1 amount to your wallet.',
                        $currencySymbol.$finalmaximumAmount
                    )
                );
            } elseif ($amount < $finalminimumAmount) {
                $amount = $finalminimumAmount;
                $this->messageManager->addNotice(
                    __(
                        'You can not add less than %1 amount to your wallet.',
                        $currencySymbol.$finalminimumAmount
                    )
                );
            }
            $cart->setCustomPrice($amount);
            $cart->setOriginalCustomPrice($amount);
            $cart->setQty(1);
            $cart->getProduct()->setIsSuperMode(true);
            $cart->setRowTotal($amount);
            if (!$this->helper->getDiscountEnable()) {
                $cart->setNoDiscount(1);
            } else {
                $cart->setNoDiscount(0);
            }
            $cart->save();
            $this->checkoutSession->getQuote()->setItemsQty(1);
            $this->checkoutSession->getQuote()->setSubtotal($amount);
            $this->checkoutSession->getQuote()->setGrandTotal($amount);
            $flag = 1;
        }
        return $flag;
    }

    /**
     * Single Product Added function
     *
     * @param object $cartData
     * @param array $params
     * @param integer $finalmaximumAmount
     * @param integer $finalminimumAmount
     */
    public function singleProductAdded($cartData, $params, $finalmaximumAmount, $finalminimumAmount)
    {
        $itemId = '';
        $price = 0;
        $flag = 0;
        $productId = $params['product'];
        $walletProductId = $this->helper->getWalletProductId();
        foreach ($cartData as $cart) {
            $itemId = $cart->getItemId();
            $price = $cart->getCustomPrice();
        }
        foreach ($cartData as $cart) {
            if ($cart->getProductId() == $productId) {
                if ($productId == $walletProductId) {
                    $flag = $this->updateWalletProductItem(
                        $cart,
                        $itemId,
                        $price,
                        $finalmaximumAmount,
                        $finalminimumAmount
                    );
                }
            }
            if ($flag == 1) {
                break;
            }
        }
        if ($itemId != '' && $flag == 1) {
            $quote = $this->itemFactory->create()->load($itemId);
            $quote->delete();
        }
    }

    /**
     * Reorder Wallet Product Add function
     *
     * @param object $cartData
     * @param array $itemIds
     * @param integer $price
     * @param integer $finalmaximumAmount
     * @param integer $finalminimumAmount
     */
    public function reorderWalletProductAdd($cartData, $itemIds, $price, $finalmaximumAmount, $finalminimumAmount)
    {
        $walletProductId = $this->helper->getWalletProductId();
        $flag = 0;
        foreach ($cartData as $cart) {
            if ($cart->getProduct()->getId() == $walletProductId) {
                $flag = $this->updateWalletProductItem(
                    $cart,
                    $itemIds,
                    $price,
                    $finalmaximumAmount,
                    $finalminimumAmount
                );
            }
            if ($flag == 1) {
                break;
            }
        }
        if ($itemIds != '' && $flag == 1) {
            $quote = $this->itemFactory->create()->load($itemIds);
            $quote->delete();
        }
    }
}
