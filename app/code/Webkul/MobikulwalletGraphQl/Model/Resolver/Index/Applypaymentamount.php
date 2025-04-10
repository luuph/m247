<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulwalletGraphQl
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c)Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MobikulwalletGraphQl\Model\Resolver\Index;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\ObjectManager;

class Applypaymentamount extends AbstractWallet implements ResolverInterface
{
    protected $wallet;

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->wholeData = $args;
        try {
            $this->verifyRequest();
            $environment     = $this->_emulate->startEnvironmentEmulation($this->storeId);
            $quoteCollection = $this->quote->getCollection()
                ->addFieldToFilter("customer_id", $this->customerId)
                ->addFieldToFilter("store_id", $this->storeId)
                ->addFieldToFilter("is_active", 1)
                ->addOrder("updated_at", "DESC");
            $quote = $quoteCollection->getFirstItem();
            $this->_checkoutSession->setId($quote->getId());
            $customerSession = ObjectManager::getInstance()->get(\Magento\Customer\Model\SessionFactory::class)->create();
            $customerSession->setCustomerId($this->customerId);
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
            $leftAmountToPay  = 0;
            if ($this->wallet == "set") {
                if ($converttedAmount >= $grandtotal) {
                    $discountAmount = $grandtotal;
                } else {
                    $discountAmount  = $converttedAmount;
                    $leftAmountToPay = $grandtotal - $converttedAmount;
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
                $walletData = [
                    "formattedLeftInWallet"      => $leftinWallet,
                    "formattedPaymentToMade"     => $this->_walletHelper->getformattedPrice($grandtotal),
                    "unformattedLeftInWallet"    => $baseLeftAmount,
                    "formattedAmountInWallet"    => $this->_walletHelper->getformattedPrice($amount),
                    "unformattedPaymentToMade"   => $grandtotal,
                    "formattedLeftAmountToPay"   => $this->_walletHelper->getformattedPrice($leftAmountToPay),
                    "unformattedAmountInWallet"  => $amount,
                    "unformattedLeftAmountToPay" => $leftAmountToPay
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
                $walletData = [
                    "formattedLeftInWallet"      => $leftinWallet,
                    "formattedPaymentToMade"     => $this->_walletHelper->getformattedPrice($grandtotal),
                    "unformattedLeftInWallet"    => $amount,
                    "formattedAmountInWallet"    => $this->_walletHelper->getformattedPrice($amount),
                    "unformattedPaymentToMade"   => $grandtotal,
                    "formattedLeftAmountToPay"   => $this->_walletHelper->getformattedPrice($leftAmountToPay),
                    "unformattedAmountInWallet"  => $amount,
                    "unformattedLeftAmountToPay" => $leftAmountToPay
                ];
                $this->_checkoutSession->setWalletDiscount($walletValue);
            }
            $this->returnArray["success"]    = true;
            $this->returnArray["walletData"] = $walletData;
            $this->_emulate->stopEnvironmentEmulation($environment);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            return $this->returnArray;
        }
    }

    /**
     * Function verify Request to authenticate the request
     *
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    public function verifyRequest()
    {
        if ($this->wholeData) {
            $this->storeId       = $this->wholeData["storeId"];
            $this->wallet        = $this->wholeData["wallet"];
            // $this->grandtotal    = $this->wholeData["grandtotal"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->customerId    = $this->_helper->getCustomerByToken($this->customerToken) ?? 0;
            if ($this->customerId == 0) {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist, so you need to logout.")
                );
            } elseif ($this->customerId != 0) {
                $this->customer = $this->_customerFactory->create()->load($this->customerId);
                $this->_customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException("Invalid Request");
        }
    }
}
