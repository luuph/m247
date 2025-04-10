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
 * RemoveCartItem resolver
 */
class RemoveCartItem extends AbstractCheckout implements ResolverInterface
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
            $quote->removeItem($this->itemId);
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->collectTotals()->save();
            if ($quote->getItemsQty()*1 > 0) {
                $totals = [];
                if ($quote->isVirtual()) {
                    $totals = $quote->getBillingAddress()->getTotals();
                } else {
                    $totals = $quote->getShippingAddress()->getTotals();
                }
                $catalogHelper = $this->helperCatalog;
                $checkoutHelper = $this->checkoutHelper;
                $subtotal = [];
                $discount = [];
                $grandtotal = [];
                $shipping = [];
                if (isset($totals["subtotal"])) {
                    $subtotal = $totals["subtotal"];
                    $this->returnArray["subtotal"]["title"] = $subtotal->getTitle();
                    $this->returnArray["subtotal"]["value"] = $catalogHelper->stripTags(
                        $checkoutHelper->formatPrice($subtotal->getValue())
                    );
                    $this->returnArray["subtotal"]["unformattedValue"] = $subtotal->getValue();
                }
                if (isset($totals["discount"])) {
                    $discount = $totals["discount"];
                    $this->returnArray["discount"]["title"] = $discount->getTitle();
                    $this->returnArray["discount"]["value"] = $catalogHelper->stripTags(
                        $checkoutHelper->formatPrice($discount->getValue())
                    );
                    $this->returnArray["discount"]["unformattedValue"] = $discount->getValue();
                }
                if (isset($totals["shipping"])) {
                    $shipping = $totals["shipping"];
                    $this->returnArray["shipping"]["title"] = $shipping->getTitle();
                    $this->returnArray["shipping"]["value"] = $catalogHelper->stripTags(
                        $checkoutHelper->formatPrice($shipping->getValue())
                    );
                    $this->returnArray["shipping"]["unformattedValue"] = $shipping->getValue();
                }
                if (isset($totals["tax"])) {
                    $tax = $totals["tax"];
                    $this->returnArray["tax"]["title"] = $tax->getTitle();
                    $this->returnArray["tax"]["value"] = $catalogHelper->stripTags(
                        $checkoutHelper->formatPrice($tax->getValue())
                    );
                    $this->returnArray["tax"]["unformattedValue"] = $tax->getValue();
                }
                if (isset($totals["grand_total"])) {
                    $grandtotal = $totals["grand_total"];
                    $this->returnArray["grandtotal"]["title"] = $grandtotal->getTitle();
                    $this->returnArray["grandtotal"]["value"] = $catalogHelper->stripTags(
                        $checkoutHelper->formatPrice($grandtotal->getValue())
                    );
                    $this->returnArray["grandtotal"]["unformattedValue"] = $grandtotal->getValue();
                }
            }
            $this->returnArray["success"] = true;
            
            $this->returnArray["cartCount"] = (int) $this->helper->getCartCount($quote);
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
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
            $this->itemId = $this->wholeData["itemId"] ?? 0;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
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
