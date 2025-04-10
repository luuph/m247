<?php
namespace Unveels\OrderSplits\Model;

use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteManagement as CoreQuoteManagement;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\ProductRepositoryInterface;

class QuoteManagement extends CoreQuoteManagement
{
    /**
     * Place an order and split it by supplier first, then group by stored_locally.
     *
     * @param int $cartId
     * @param PaymentInterface|null $paymentMethod
     * @return int
     * @throws LocalizedException
     */
    public function placeOrder($cartId, PaymentInterface $paymentMethod = null)
    {
        $quote = $this->quoteRepository->getActive($cartId);
        $paymentMethodString = $quote->getPayment()->getMethod();

        // Get billing and shipping address data
        $billingAddress = $quote->getBillingAddress()->getData();
        $shippingAddress = $quote->getShippingAddress()->getData();
        unset($billingAddress['id'], $billingAddress['quote_id']);
        unset($shippingAddress['id'], $shippingAddress['quote_id']);

        $orders = [];
        $orderIds = [];
        $itemsBySeller = [];
        $storedLocallyItems = [];
        $objectManager = ObjectManager::getInstance();
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/QuoteManagement2.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('start');
        // Separate items into supplier items and non-supplier items grouped by stored_locally
        foreach ($quote->getAllItems() as $item) {
            $product = $productRepository->getById($item->getProduct()->getId());
            $storedLocally = $product->getCustomAttribute('stored_locally') ? $product->getCustomAttribute('stored_locally')->getValue() : 0;
            $logger->info('start 1');
            $logger->info($storedLocally);

            if ($storedLocally) {
                // Group all supplier products into one order
                $storedLocallyItems[] = $item;
                $logger->info('start 2');
            } else {
                // Group non-supplier products by stored_locally
                $sellerId = $product->getCustomAttribute('supplier') ? $product->getCustomAttribute('supplier')->getValue() : null;
                if (!isset($itemsBySeller[$sellerId])) {
                    $itemsBySeller[$sellerId] = [];
                }
                $itemsBySeller[$sellerId][] = $item;
                $logger->info('start 2_1');
                // $logger->info($sellerId);
            }
        }

        // Process supplier items as a separate order
        if (!empty($storedLocallyItems)) {
            $logger->info('start 3');
            $order = $this->createSplitOrder($quote, $storedLocallyItems, $paymentMethod, $billingAddress, $shippingAddress, $paymentMethodString);
            $orders[] = $order;
            $orderIds[$order->getId()] = $order->getIncrementId();
            $logger->info(print_r($orderIds,true));
        }

        // Process each seller's items as separate orders
        foreach ($itemsBySeller as $sellerId => $items) {
            $logger->info('start 4');
            $order = $this->createSplitOrder($quote, $items, $paymentMethod, $billingAddress, $shippingAddress, $paymentMethodString);
            $orders[] = $order;
            $orderIds[$order->getId()] = $order->getIncrementId();
            $logger->info(print_r($orderIds,true));
        }

        $logger->info('start 5');
        // Deactivate the original quote
        $quote->setIsActive(false);
        $this->quoteRepository->save($quote);

        $logger->info('start 6');
        // Update session data with the last order information
        $lastOrder = end($orders);
        $this->checkoutSession->setLastQuoteId($quote->getId());
        $this->checkoutSession->setLastSuccessQuoteId($quote->getId());
        $this->checkoutSession->setLastOrderId($lastOrder->getId());
        $this->checkoutSession->setLastRealOrderId($lastOrder->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($lastOrder->getStatus());
        $this->checkoutSession->setOrderIds($orderIds);
        $logger->info('start 7');
        // Dispatch an event after all orders are submitted
        $this->eventManager->dispatch('checkout_submit_all_after', ['orders' => $orders, 'quote' => $quote]);
        $logger->info('start 8');

        return $this->getOrderKeys($orderIds);
    }

    /**
     * Create a split order from a list of items.
     *
     * @param $quote
     * @param array $items
     * @param PaymentInterface|null $paymentMethod
     * @param array $billingAddress
     * @param array $shippingAddress
     * @param string $paymentMethodString
     * @return \Magento\Sales\Model\Order
     * @throws LocalizedException
     */
    private function createSplitOrder($quote, $items, $paymentMethod, $billingAddress, $shippingAddress, $paymentMethodString)
    {
        // Initialize a new quote for the items
        $quoteSplit = $this->quoteFactory->create();
        $quoteSplit->setStoreId($quote->getStoreId());
        $quoteSplit->setCustomer($quote->getCustomer());
        $quoteSplit->setCustomerIsGuest($quote->getCustomerIsGuest());

        if ($quote->getCheckoutMethod() === self::METHOD_GUEST) {
            $quoteSplit->setCustomerEmail($quote->getBillingAddress()->getEmail());
            $quoteSplit->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
        }

        $this->quoteRepository->save($quoteSplit);

        // Add items to the new quote
        foreach ($items as $item) {
            $item->setId(null); // Reset the item ID
            $quoteSplit->addItem($item);
        }

        // Set addresses for the split quote
        $quoteSplit->getBillingAddress()->setData($billingAddress);
        $quoteSplit->getShippingAddress()->setData($shippingAddress);

        // Ensure correct shipping cost for each split quote (optional)
        $quoteSplit->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates();

        // Reset totals to collect for the new quote
        $quoteSplit->setTotalsCollectedFlag(false)->collectTotals();

        // Calculate the subtotal and grand total for the split order
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item->getPrice();
        }
        $shippingAmount = $quoteSplit->getShippingAddress()->getShippingAmount();
        $taxAmount = $quoteSplit->getShippingAddress()->getTaxAmount();
        $discountAmount = $quoteSplit->getShippingAddress()->getDiscountAmount();
        $grandTotal = $subtotal + $shippingAmount + $taxAmount - $discountAmount;

        // Set the payment method for the split quote
        $quoteSplit->getPayment()->setMethod($paymentMethodString);

        if ($paymentMethod) {
            $quoteSplit->getPayment()->setQuote($quoteSplit);
            $quoteSplit->getPayment()->importData($paymentMethod->getData());
        }

        // Dispatch the event before submitting the quote
        $this->eventManager->dispatch('checkout_submit_before', ['quote' => $quoteSplit]);
        $this->quoteRepository->save($quoteSplit);

        // Submit the split quote to create the order
        $order = $this->submit($quoteSplit);

        // Update order totals to ensure everything is saved correctly
        $order->setSubtotal($subtotal);
        $order->setBaseSubtotal($subtotal);
        $order->setGrandTotal($grandTotal);
        $order->setBaseGrandTotal($grandTotal);
        $order->setSubtotalInclTax($subtotal + $taxAmount);
        $order->setBaseSubtotalInclTax($subtotal + $taxAmount);
        $order->save();

        return $order;
    }

    private function getOrderKeys($orderIds)
    {
        $orderValues = [];
        foreach (array_keys($orderIds) as $orderKey) {
            $orderValues[] = (string) $orderKey;
        }
        return array_values($orderValues);
    }
}
