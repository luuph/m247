<?php

namespace Minicart\Qtychange\Controller\Cart;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class Remove extends Action
{
    protected $checkoutSession;
    protected $quoteRepository;
    protected $resultJsonFactory;
    protected $logger;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $requestData = $this->getRequest()->getParams();
            $productSku = $requestData['sku'] ?? null;

            if (!$productSku) {
                return $result->setData(['success' => false, 'message' => 'Invalid request data.']);
            }

            // Retrieve the current cart
            $quote = $this->checkoutSession->getQuote();

            if (!$quote || !$quote->getId()) {
                return $result->setData(['success' => false, 'message' => 'No active guest cart found.']);
            }

            // Find the item in the cart by SKU and remove it
            $items = $quote->getAllItems();
            $itemFound = false;

            foreach ($items as $item) {
                if ($item->getSku() === $productSku) {
                    $quote->removeItem($item->getItemId());
                    $itemFound = true;
                    break;
                }
            }

            if (!$itemFound) {
                return $result->setData(['success' => false, 'message' => 'Item not found in cart.']);
            }

            // Save the updated quote
            $this->quoteRepository->save($quote);

            return $result->setData([
                'success' => true, 
                'message' => 'Item removed successfully.',
                'subtotal' => $quote->getSubtotal()
            ]);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $result->setData(['success' => false, 'message' => 'An error occurred while removing the item.']);
        }
    }
}
