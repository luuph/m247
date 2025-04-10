<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulwalletGraphQl
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MobikulwalletGraphQl\Plugin\Model\Resolver\Checkout;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class PlaceOrder
{
    protected $_walletHelper;
    protected $_checkoutSession;
    protected $quote;
    protected $helper;
    protected $wallet;
    protected $storeId;
    protected $customerId;
    protected $returnArray;
    protected $customersession;

    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\Quote $quote,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Customer\Model\SessionFactory $customersession
    ) {
        $this->_walletHelper    = $walletHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->quote            = $quote;
        $this->helper           = $helper;
        $this->customersession = $customersession;
    }

    /**
     * Plugin function beforeResolve
     *
     * @param \Webkul\MobikulApiGraphQl\Model\Resolver\Customer\OrderList $subject
     * @param \Closure $proceed
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return void
     */
    public function aroundResolve(
        \Webkul\MobikulApiGraphQl\Model\Resolver\Checkout\PlaceOrder $subject,
        \Closure $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->wallet = $args['wallet'] ?? "";
        $this->storeId = $args["storeId"];
        $customerToken  = $args["customerToken"] ?? '';
        if($this->_checkoutSession->getWalletDiscount() == null || $this->_checkoutSession->getWalletDiscount() == []) {
            $this->customerId = $this->helper->getCustomerByToken($customerToken) ?? 0;
            $this->customersession->create()->setCustomerId($this->customerId);
            $quoteCollection = $this->quote->getCollection()
                    ->addFieldToFilter("customer_id", $this->customerId)
                    ->addFieldToFilter("store_id", $this->storeId)
                    ->addFieldToFilter("is_active", 1)
                    ->addOrder("updated_at", "DESC");
            $quote = $quoteCollection->getFirstItem();
            $this->_checkoutSession->setId($quote->getId());
            $subtotal = $quote->getSubtotal();
            $shippingAmount = $quote->getShippingAddress()->getShippingAmount();
            $cartDiscountamount = $quote->getShippingAddress()->getDiscountAmount();
            if ($cartDiscountamount == null || $cartDiscountamount == 0) {
                $cartDiscountamount = $quote->getBillingAddress()->getDiscountAmount();
            }
            $totals = $quote->getTotals();
            $taxAmount = 0;
            if (array_key_exists("tax", $totals)) {
                $taxAmount = $totals["tax"]->getValue();
            }
            if ($taxAmount == 0) {
                foreach ($quote->getAllItems() as $item) {
                    $taxAmount = $taxAmount + $item->getTaxAmount();
                }
            }
            $grandtotal = $subtotal + $shippingAmount + $taxAmount;
            if ($cartDiscountamount != null) {
                $grandtotal = $grandtotal + $cartDiscountamount;
            }
            $grandtotal       = (float) $grandtotal;
            $grandtotal       = round($grandtotal, 4);
            $amount           = $this->_walletHelper->getWalletTotalAmount($this->customerId);
            $store            = $this->_walletHelper->getStore();
            $converttedAmount = $this->_walletHelper->currentCurrencyAmount($amount, $store);
            if ($this->wallet == "set") {
                if ($converttedAmount >= $grandtotal) {
                    $discountAmount = $grandtotal;
                } else {
                    $discountAmount  = $converttedAmount;
                }
                $left           = $converttedAmount - $discountAmount;
                $baseLeftAmount = $this->_walletHelper->baseCurrencyAmount($left);
                $leftinWallet   = $this->_walletHelper->getformattedPrice($baseLeftAmount > 0 ? $baseLeftAmount : 0);
                $walletValue    = [
                    "flag"         => 1,
                    "type"         => $this->wallet,
                    "amount"       => $discountAmount,
                    "grand_total"  => $grandtotal,
                    "leftinWallet" => $leftinWallet
                ];
                $this->_checkoutSession->setWalletDiscount($walletValue);
            } else {
                $leftinWallet = $this->_walletHelper->getformattedPrice($amount);
                $walletValue = [
                    "flag"         => 0,
                    "type"         => $this->wallet,
                    "amount"       => 0,
                    "grand_total"  => $grandtotal,
                    "leftinWallet" => $leftinWallet
                ];
                $this->_checkoutSession->setWalletDiscount($walletValue);
            }
            $quote->collectTotals()->save();
        }
      
        $this->returnArray = $proceed($field, $context, $info, $value, $args);
        return $this->returnArray;
    }
}
