<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_QuoteExtension
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Plugin\Quote;

class GetActiveQuote
{
    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $onePage;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Bss\QuoteExtension\Model\ManageQuoteRepository
     */
    protected $manageQuoteRepository;

    /**
     * @var \Bss\QuoteExtension\Model\QuoteCustomerGroupId
     */
    protected $quoteCustomerGroupId;

    /**
     * @var \Bss\QuoteExtension\Model\Type\Onepage
     */
    protected $modelOnePage;

    /**
     * Construct
     *
     * @param \Bss\QuoteExtension\Model\Type\Onepage $modelOnePage
     * @param \Bss\QuoteExtension\Model\QuoteCustomerGroupId $quoteCustomerGroupId
     * @param \Magento\Checkout\Model\Type\Onepage $onePage
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\QuoteExtension\Model\ManageQuoteRepository $manageQuoteRepository
     */
    public function __construct(
        \Bss\QuoteExtension\Model\Type\Onepage $modelOnePage,
        \Bss\QuoteExtension\Model\QuoteCustomerGroupId $quoteCustomerGroupId,
        \Magento\Checkout\Model\Type\Onepage $onePage,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Psr\Log\LoggerInterface $logger,
        \Bss\QuoteExtension\Model\ManageQuoteRepository $manageQuoteRepository
    ) {
        $this->modelOnePage = $modelOnePage;
        $this->quoteCustomerGroupId = $quoteCustomerGroupId;
        $this->onePage = $onePage;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->manageQuoteRepository = $manageQuoteRepository;
    }

    /**
     * Replace quote in onePage
     *
     * @param object $subject
     * @return void
     */
    public function beforeGetActiveQuote($subject)
    {
        $params = $subject->getRequest()->getParams();
        if (isset($params['quoteextension']) && $params['quoteextension'] == 1) {
            $quoteId = $params['quote_id'];
            try {
                if ($this->onePage->getQuote()->getId() != $quoteId) {
                    $manageQuote = $this->manageQuoteRepository->getByQuoteId($quoteId);
                    $quote = $this->quoteRepository->get($quoteId);
                    $quote->setIsSuperMode(true);
                    $quote = $this->quoteCustomerGroupId->getQuoteView(
                        $quote,
                        $manageQuote->getCustomerId(),
                        $manageQuote
                    );
                    $this->modelOnePage->setQuote($quote);
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }
    }

    /**
     * Check quote can one-step checkout order
     *
     * @param \Bss\OneStepCheckout\Controller\Index\Index|object $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsQuoteCanOrder($subject, $result)
    {
        $params = $subject->getRequest()->getParams();
        if (isset($params['quote_id']) && isset($params['quoteextension']) && $params['quoteextension'] == 1) {
            $quote_id = $params['quote_id'];
            try {
                $manageQuote = $this->manageQuoteRepository->getByQuoteId($quote_id);
                if (!$manageQuote->getQuoteId()) {
                    return false;
                }
                if ($manageQuote->getStatus() == \Bss\QuoteExtension\Model\Config\Source\Status::STATE_ORDERED) {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return $result;
    }
}
