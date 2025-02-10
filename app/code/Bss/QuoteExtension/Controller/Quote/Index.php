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
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Controller\Quote;

use Bss\QuoteExtension\Model\QuoteExtension as CustomerQuoteExtension;

/**
 * Class Index
 *
 * @package Bss\QuoteExtension\Controller\Quote
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Index extends \Bss\QuoteExtension\Controller\Quote
{
    /**
     * Shopping cart display action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultReidrect = $this->resultRedirectFactory->create();

        if ($this->isInvalidQuoteRequest()) {
            return $resultReidrect->setPath('quoteextension/quote/emptyQuote');
        }
        $this->quoteExtensionSession->setCheckoutIsQuoteExtension(false);
        $quoteId = $this->quoteExtensionSession->getQuoteExtensionId();
        $this->quoteExtensionSession->setIsQuoteExtension($quoteId);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Quote'));

        return $resultPage;
    }
}
