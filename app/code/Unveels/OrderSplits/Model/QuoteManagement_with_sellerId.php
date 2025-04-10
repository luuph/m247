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
     * Place an order and split it by individual items grouped by seller_id.
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
        $objectManager = ObjectManager::getInstance();
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);

        foreach ($quote->getAllItems() as $item) {
            $product = $productRepository->getById($item->getProduct()->getId());
            $sellerId = $product->getCustomAttribute('seller_id') ? $product->getCustomAttribute('seller_id')->getValue() : null;
            
            if (!isset($itemsBySeller[$sellerId])) {
                $itemsBySeller[$sellerId] = [];
            }
            $itemsBySeller[$sellerId][] = $item;
        }

        // Process each seller's items as a separate order
        foreach ($itemsBySeller as $sellerId => $items) {
            // Initialize a new quote for each seller group
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

            // Logging
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/QuoteManagement.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('Calculating totals for split order');
            $logger->info('Subtotal: ' . $subtotal);
            $logger->info('Grand Total: ' . $grandTotal);

            // Set the payment method for the split quote
            $quoteSplit->getPayment()->setMethod($paymentMethodString);

            if ($paymentMethod) {
                $quoteSplit->getPayment()->setQuote($quoteSplit);
                $data = $paymentMethod->getData();
                $logger->info('Payment Data: ' . print_r($data, true));
                $quoteSplit->getPayment()->importData($data);
            }

            // Dispatch the event before submitting the quote
            $this->eventManager->dispatch('checkout_submit_before', ['quote' => $quoteSplit]);
            $this->quoteRepository->save($quoteSplit);

            // Log the split quote data
            $logger->info('Split Quote Data: ' . print_r($quoteSplit->getData(), true));
            $order = $this->submit($quoteSplit);

            // Update order totals to ensure everything is saved correctly
            $order->setSubtotal($subtotal);
            $order->setBaseSubtotal($subtotal);
            $order->setGrandTotal($grandTotal);
            $order->setBaseGrandTotal($grandTotal);
            $order->setSubtotalWithDiscount($grandTotal);
            $order->setBaseSubtotalWithDiscount($grandTotal);
            $order->setSubtotalInclTax($subtotal + $taxAmount);
            $order->setBaseSubtotalInclTax($subtotal + $taxAmount);
            $order->setTotalDue($grandTotal);
            $order->setBaseTotalDue($grandTotal);
            $order->setShippingAmount($shippingAmount);
            $order->setBaseShippingAmount($shippingAmount);
            $order->setTaxAmount($taxAmount);
            $order->setBaseTaxAmount($taxAmount);

            // Save the order with updated totals
            $order->save();

            // Log the order data after saving
            $logger->info('Order Data After Save: ' . print_r($order->debug(), true));
            $orders[] = $order;
            $orderIds[$order->getId()] = $order->getIncrementId();

            if (null === $order) {
                throw new LocalizedException(
                    __('An error occurred on the server. Please try to place the order again.')
                );
            }
        }

        // Deactivate the original quote
        $quote->setIsActive(false);
        $this->quoteRepository->save($quote);

        // Update session data with the last order information
        $this->checkoutSession->setLastQuoteId($quoteSplit->getId());
        $this->checkoutSession->setLastSuccessQuoteId($quoteSplit->getId());
        $this->checkoutSession->setLastOrderId($order->getId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($order->getStatus());
        $this->checkoutSession->setOrderIds($orderIds);

        // Dispatch an event after all orders are submitted
        $this->eventManager->dispatch('checkout_submit_all_after', ['orders' => $orders, 'quote' => $quote]);

        return $this->getOrderKeys($orderIds);
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
