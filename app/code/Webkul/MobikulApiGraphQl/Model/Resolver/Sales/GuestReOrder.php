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

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Sales;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Webkul\MobikulApiGraphQl\Model\Resolver\Customer\AbstractCustomer;

/**
 * GuestReOrder resolver
 */
class GuestReOrder extends AbstractCustomer implements ResolverInterface
{
    /**
     * QuoteFactory variable
     *
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * Construct function
     *
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @return void
     */
    public function _construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->quoteFactory = $quoteFactory;
    }

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
            $order = $this->order->loadByIncrementId($this->incrementId);

            $outOfStockItems = [];
            if ($this->quoteId) {
                $quote = $this->quoteFactory->create()->load($this->quoteId);
                if (!$quote->getId()) {
                    $this->returnArray["message"] = __("Quote not found.");
                    return $this->returnArray;
                }
            } else {
                $quote = $this->quoteFactory->create()
                    ->setStoreId($this->storeId)
                    ->setIsActive(true)
                    ->setIsMultiShipping(false)
                    ->save();
            }
            $quoteId = (int) $quote->getId();
            
            $cart = $this->cartFactory->create()->setQuote($quote);
            $items = $order->getItemsCollection();
            $this->checkoutSession->setQuoteId($quote->getId());
            foreach ($items as $item) {
                $cart->addOrderItem($item);
            }
            
            foreach ($quote->getAllItems() as $quoteItem) {
                if ($quoteItem->getProductId() == $this->checkoutSession->getLastAddedProductId()) {
                    $quoteItem->setQty($quoteItem->getData("qty"))->save(); //To update the quantity in quote item
                }
            }
            $cart->save();
            
            $this->returnArray["cartCount"] = (int) $this->helper->getCartCount($quote);
            $this->returnArray['quoteId'] = $quoteId;
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Product(s) has been added to cart.");
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * Verify Request function to verify the request
     *
     * @return void|jSon
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->incrementId = $this->wholeData["incrementId"] ?? "";
            $this->quoteId = $this->wholeData["quoteId"] ?? "";
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
