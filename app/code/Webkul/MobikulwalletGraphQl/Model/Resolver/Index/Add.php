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

/**
 * Product Add to Cart resolver
 */
class Add extends AbstractWallet implements ResolverInterface
{
    protected $price;
    protected $currency;

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
            $quote = new \Magento\Framework\DataObject();
            $quoteCollection = $this->_quoteFactory->create()
                ->getCollection()
                ->addFieldToFilter("customer_id", $this->customerId)
                ->addFieldToFilter("store_id", $this->storeId)
                ->addFieldToFilter("is_active", 1)
                ->addOrder("updated_at", "DESC");
            $quote   = $quoteCollection->getFirstItem();
            $quoteId = $quote->getId();
            if ($quote->getId() < 0 || !$quoteId) {
                $quote = $this->_quoteFactory->create()
                    ->setStoreId($this->storeId)
                    ->setIsActive(true)
                    ->setIsMultiShipping(false)
                    ->save();
                $quoteId = (int) $quote->getId();
                $customer = $this->_customerRepository->getById($this->customerId);
                $quote->assignCustomer($customer);
                $quote->setCustomer($customer);
                $quote->getBillingAddress();
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->collectTotals()->save();
            }
            $cart      = $this->_cartFactory->create();
            $cartItems = $quote->getAllVisibleItems();
            $minimumAmount = 0;
            $maximumAmount = 0;
            $maximumAmount = $this->_walletHelper->getMaximumAmount();
            $minimumAmount = $this->_walletHelper->getMinimumAmount();
            if ($minimumAmount > $maximumAmount) {
                $temp          = $maximumAmount;
                $maximumAmount = $minimumAmount;
                $minimumAmount = $temp;
            }
            $baseCurrenyCode = $this->_walletHelper->getBaseCurrencyCode();
            $currencySymbol  = $this->_walletHelper->getCurrencySymbol(
                $this->_walletHelper->getCurrentCurrencyCode()
            );
            $currentCurrenyCode = $this->_walletHelper->getCurrentCurrencyCode();
            $adminConfigPrice   = $minimumAmount;
            $minimumAmount = $this->_walletHelper->getwkconvertCurrency(
                $baseCurrenyCode,
                $currentCurrenyCode,
                $adminConfigPrice
            );
            $walletProductId = $this->_walletHelper->getWalletProductId();
            $product = $this->_productFactory->create()->setStoreId($this->storeId)->load($this->productId);
            $this->returnArray["message"] = __("%1 is added to your shopping cart.",
            $this->_helperCatalog->stripTags($product->getName()));
            $itemProId = 0;
            foreach ($cartItems as $item)
                $itemProId = $item->getProductId();
            if (count($cartItems) > 1 || (count($cartItems) == 1 && $this->productId != $itemProId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("You can not add other products with wallet product, and vise versa.")
                );
            } else {
                if ($this->price > $maximumAmount) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("You can not add more than %1 amount to your wallet.",
                        $this->stripTags($this->_priceFormat->currency($maximumAmount)))
                    );
                } else if ($this->price < $minimumAmount) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("You can not add less than %1 amount to your wallet.", $currencySymbol . $minimumAmount)
                    );
                }
            }
            if (!count($cartItems)) {
                $params = ["price" => $this->price, "qty" => $this->qty, "product" => $this->product];
                $this->request->setParams($params);
                $cart->setQuote($quote)->addProduct($product, $params)->save();
            }
            foreach ($cartItems as $item) {
                $price = $item->getCustomPrice() + $this->price;
                if ($item->getProduct()->getId() == $walletProductId) {
                    $item->setCustomPrice($price);
                    $item->setOriginalCustomPrice($price);
                    $item->setQty(1);
                    $item->getProduct()->setIsSuperMode(true);
                    $item->setRowTotal($price);
                    $item->save();
                }
            }
            $this->returnArray["success"] = true;
            $quote->collectTotals()->save();
            $this->returnArray["cartCount"] = $quote->getItemsQty() * 1;
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
            $this->qty           = $this->wholeData["qty"];
            $this->price         = $this->wholeData["price"];
            $this->storeId       = $this->wholeData["storeId"];
            $this->productId     = $this->wholeData["productId"];
            $this->currency      = $this->wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
            $this->currency      = $this->getRequest()->getHeader(self::CURRENT_CURRENCY) ?? $this->currency;
            $this->store->setCurrentCurrencyCode($this->currency);
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
