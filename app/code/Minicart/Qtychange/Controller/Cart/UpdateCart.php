<?php

namespace Minicart\Qtychange\Controller\Cart;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class UpdateCart extends Action
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
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/UpdateCart.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('text message');

        $result = $this->resultJsonFactory->create();
        $logger->info('text message2');

        try {
            $requestData = $this->getRequest()->getParams();
            $logger->info(print_r($requestData,1));
            $logger->info('text message3');

            // Get the product SKU and new quantity from the request
            $productSku = $requestData['sku'] ?? null;
            $newQty = $requestData['qty'] ?? null;
            $logger->info('text message4');

            if (!$productSku || !$newQty) {
                return $result->setData(['success' => false, 'message' => 'Invalid request data.']);
            }
            $logger->info('text message5');

            // Retrieve the current guest quote
            $quote = $this->checkoutSession->getQuote();

            if (!$quote || !$quote->getId()) {
                return $result->setData(['success' => false, 'message' => 'No active guest cart found.']);
            }

            // Find the item in the cart by SKU
            $items = $quote->getAllItems();
            $itemFound = false;

            foreach ($items as $item) {
                if ($item->getSku() === $productSku) {
                    $item->setQty($newQty);
                    $itemFound = true;
                    break;
                }
            }

            if (!$itemFound) {
                return $result->setData(['success' => false, 'message' => 'Item with SKU not found in the cart.']);
            }

            // Save the updated quote
            $this->quoteRepository->save($quote);

            return $result->setData(['success' => true, 'message' => 'Cart updated successfully.','subtotal' => $quote->getSubtotal()]);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $result->setData(['success' => false, 'message' => 'An error occurred while updating the cart.']);
        }
    }
}