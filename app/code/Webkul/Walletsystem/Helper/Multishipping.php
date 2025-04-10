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

namespace Webkul\Walletsystem\Helper;

use Webkul\Walletsystem\Helper\Data;

class Multishipping extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected $checkoutModel;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quote;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $checkoutModel
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\Walletsystem\Helper\Data $helper
     * @param \Magento\Quote\Model\QuoteFactory $quote
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $checkoutModel,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Quote\Model\QuoteFactory $quote
    ) {
        parent::__construct($context);
        $this->checkoutModel = $checkoutModel;
        $this->request = $request;
        $this->helper = $helper;
        $this->quote = $quote;
    }

    /**
     * Create OrderData After Wallet Payment
     *
     * @return array $orderData
     */
    public function getOrderData()
    {
        $order = $this->getCheckoutSession()->getQuote();
        $customerId = $this->helper->getCustomerId();
        $paymentMethod = $order->getPayment()->getMethod();
        $walletTotalAmount = $this->helper->getWalletTotalAmount($customerId);
        $grandTotal = $order->getGrandTotal();
        $baseGrandTotal = $order['base_grand_total'];
        $subtotal = $order['subtotal'];
        $shippingAddressCount = $this->shippingAddressCount();
                 
        $leftinWallet = $walletTotalAmount - $grandTotal;
        $subtotal = $walletTotalAmount - $subtotal;

        if ($walletTotalAmount > $grandTotal) {
            $amount = $walletTotalAmount - $leftinWallet;
            
            $grandTotal = 0 ;
        } else {
            $leftinWallet = (-1) * $leftinWallet;
            $subtotal = (-1) * $subtotal;
            $amount = $walletTotalAmount;
            $grandTotal = $leftinWallet;
            $leftinWallet = 0;
        }

        $orderData = [
            'method' => $paymentMethod,
            'used_amount' => $amount,
            'subtotal' => $subtotal,
            'leftinWallet' => $leftinWallet,
            'orderTotal' => $order->getGrandTotal()
        ];

        return $orderData;
    }

    /**
     * Get Checkout Session
     *
     * @return object
     */
    public function getCheckoutSession()
    {
        return $this->checkoutModel->getCheckoutSession();
    }

    /**
     * Count address in MultiShipping
     *
     * @return int
     */
    public function shippingAddressCount()
    {
        return count($this->getCheckoutSession()->getQuote()->getAllShippingAddresses());
    }

    /**
     * Check Payment is Partial
     *
     * @return bool
     */
    public function isPartial()
    {
        $params = $this->request->getPost();
        $method = $params['payment']['method'];
        $walletTotal = $params['wallet_subtotal'];
        $orderData = $this->getOrderData();

        if ($method != "walletsystem" && ($walletTotal != $orderData['orderTotal'] )) {
            return true;
        }
        return false;
    }
}
