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
use Magento\Quote\Model\QuoteFactory;

/**
 * Webkul Walletsystem Class
 */
class SalesQuoteAddItem implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    protected $walletHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * Initialize dependencies
     *
     * @param OrderFactory                                  $orderFactory
     * @param \Magento\Framework\App\Request\Http           $request
     * @param \Magento\Framework\Message\ManagerInterface   $messageManager
     * @param \Webkul\Walletsystem\Helper\Data              $walletHelper
     * @param \Magento\Checkout\Model\Session               $checkoutSession
     * @param QuoteFactory                                  $quoteFactory
     */
    public function __construct(
        OrderFactory $orderFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        QuoteFactory $quoteFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->walletHelper = $walletHelper;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Add quote item handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->walletHelper;
        $walletProductId = $helper->getWalletProductId();
        $amount = 0;
        $params = $this->request->getParams();
        $minimumAmount = $helper->getMinimumAmount();
        $maximumAmount = $helper->getMaximumAmount();
        $currencySymbol = $helper->getCurrencySymbol($helper->getCurrentCurrencyCode());
        $event = $observer->getQuoteItem();
        $product = $event->getProduct();
        $productId = $product->getId();
        if ($minimumAmount > $maximumAmount) {
            $temp = $minimumAmount;
            $minimumAmount = $maximumAmount;
            $maximumAmount = $temp;
        }
        $currentCurrenyCode = $helper->getCurrentCurrencyCode();
        $baseCurrenyCode = $helper->getBaseCurrencyCode();

        $finalminimumAmount = $helper->getwkconvertCurrency(
            $baseCurrenyCode,
            $currentCurrenyCode,
            $minimumAmount
        );
        $finalmaximumAmount = $helper->getwkconvertCurrency(
            $baseCurrenyCode,
            $currentCurrenyCode,
            $maximumAmount
        );
        $controllerAction = $this->request->getActionName();
        $walletAmountExists = $this->getExistsWalletPrice();
        if ($controllerAction == 'reorder') {
            $orderId = $params['order_id'];
            $order = $this->orderFactory->create()->load($orderId);
            $orderItems = $order->getAllItems();
            foreach ($orderItems as $orderItem) {
                if ($orderItem->getProductId() == $productId) {
                    if ($productId == $walletProductId) {
                        $amount = $orderItem->getPrice();
                        $this->updateWalletProductAmount(
                            $amount,
                            $finalminimumAmount,
                            $finalmaximumAmount,
                            $event,
                            $walletAmountExists
                        );
                    }
                }
            }
        } else {
            if (array_key_exists('price', $params)) {
                $amount = $params['price'];
            }
            if ($productId == $walletProductId) {
                $this->updateWalletProductAmount(
                    $amount,
                    $finalminimumAmount,
                    $finalmaximumAmount,
                    $event,
                    $walletAmountExists
                );
            }
        }
    }

    /**
     * Update wallet product amount
     *
     * @param int $amount
     * @param int $finalminimumAmount
     * @param int $finalmaximumAmount
     * @param object $event
     * @param int $walletAmountExists
     */
    public function updateWalletProductAmount(
        $amount,
        $finalminimumAmount,
        $finalmaximumAmount,
        $event,
        $walletAmountExists
    ) {
        $currencySymbol = $this->walletHelper->getCurrencySymbol(
            $this->walletHelper->getCurrentCurrencyCode()
        );
        $finalAmount = $walletAmountExists + $amount;
        $setAmount = $amount;
        if ($finalAmount > $finalmaximumAmount) {
            $setAmount = $finalmaximumAmount-$walletAmountExists;
            $this->messageManager->addNotice(
                __(
                    'You can not add more than %1 amount to your wallet.',
                    $currencySymbol.$finalmaximumAmount
                )
            );
        } elseif ($finalAmount < $finalminimumAmount) {
            $setAmount = $finalminimumAmount+$walletAmountExists;
            $this->messageManager->addNotice(
                __(
                    'You can not add less than %1 amount to your wallet.',
                    $currencySymbol.$finalminimumAmount
                )
            );
        }
        $event->setOriginalCustomPrice($setAmount);
        $event->setCustomPrice($setAmount);
        if (!$this->walletHelper->getDiscountEnable()) {
            $event->setNoDiscount(1);
        } else {
            $event->setNoDiscount(0);
        }
        $event->getProduct()->setIsSuperMode(true);
    }

    /**
     * Get existing wallet price
     *
     * @return float
     */
    public function getExistsWalletPrice()
    {
        $walletInCart = 0;
        $price = 0;
        $quote = '';
        if ($this->checkoutSession->getQuoteId()) {
            $quoteId = $this->checkoutSession->getQuoteId();
            $quote = $this->quoteFactory->create()
                ->load($quoteId);
        }
        if ($quote) {
            $cartData = $quote->getAllVisibleItems();
            if (!empty($cartData)) {
                $walletProductId = $this->walletHelper->getWalletProductId();
                foreach ($cartData as $cart) {
                    if ($cart->getProduct()->getId() == $walletProductId) {
                        $price = $cart->getCustomPrice();
                    }
                }
            }
        }
        return $price;
    }
}
