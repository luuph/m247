<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphql
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Checkout;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * UpdateCart resolver
 */
class UpdateCart extends AbstractCheckout implements ResolverInterface
{
    /**
     * @inheritdoc
     */
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
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $quote = $this->helper->getCustomerQuote($this->customerId);
            }
            if ($this->quoteId != 0) {
                $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
            }
    
            $cartData = [];
            foreach ($this->itemData as $item) {
                $cartData[$item['id']] = ["qty" => $item['qty']];
            }
    
            $filter = new \Magento\Framework\Filter\LocalizedToNormalized(
                ["locale" => $this->localeResolver->getLocale()]
            );
    
            foreach ($cartData as $index => $eachData) {
                if (isset($eachData["qty"])) {
                    $cartData[$index]["qty"] = $filter->filter(trim($eachData["qty"]));
                }
            }
    
            $this->returnArray["message"] = __("Cart updated successfully");
            $this->returnArray["success"] = true; // Default success
    
            foreach ($cartData as $itemId => $itemInfo) {
                if (!isset($itemInfo["qty"])) {
                    continue;
                }
                $qty = (float)$itemInfo["qty"];
                $quoteItem = $quote->getItemById($itemId);
                if (!$quoteItem) {
                    continue;
                }
                $product = $quoteItem->getProduct();
                if (!$product) {
                    continue;
                }
                $stockItem = $this->stockRegistry->getStockItem($product->getId());
                if (!$stockItem) {
                    continue;
                }
    
                if (($stockItem['is_in_stock'] == 1) && ($qty <= $stockItem['qty'])) {
                    $quoteItem->setQty($qty)->save();
                } else {
                    // Set failure message and success to false
                    $this->returnArray["message"] = __("The requested qty is not available.");
                    $this->returnArray["success"] = false;
                    break; // Exit loop as at least one item failed
                }
            }
    
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->collectTotals()->save();
            $this->returnArray["cartCount"] = (int)$quote->getItemsQty() * 1;
            $this->emulate->stopEnvironmentEmulation($environment);
    
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->returnArray["success"] = false; // Set success to false in case of exception
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }
    

    /**
     * Function to verify request
     *
     * @return void|json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->itemData = $this->wholeData["itemData"] ?? [];
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
