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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Plugin\Checkout\Cart;

use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

class Index
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * @var SubUserQuoteRepositoryInterface
     */
    protected $subQuoteRepository;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param Session $checkoutSession
     * @param ManagerInterface $manager
     * @param RedirectFactory $redirectFactory
     * @param SubUserQuoteRepositoryInterface $subQuoteRepository
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        SubUserQuoteRepositoryInterface $subQuoteRepository,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->manager = $manager;
        $this->subQuoteRepository = $subQuoteRepository;
        $this->redirectFactory = $redirectFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Validate cart before accessing the cart page
     *
     * @param \Magento\Checkout\Controller\Cart\Index $subject
     * @param callable $proceed
     * @return Redirect|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundExecute(\Magento\Checkout\Controller\Cart\Index $subject, $proceed)
    {
        // Dung Around de tranh xu ly logic cua proceed
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getBssIsSubQuote()) {
            $subQuote = $this->subQuoteRepository->getByQuoteId($quote->getId());
            if ($subQuote && $subQuote->getQuoteStatus() == Data::SUB_QUOTE_APPROVED) {
                $resultRedirect = $this->redirectFactory->create();
                $this->manager->addErrorMessage(
                    __('Please checkout your current approved order or go
                    "Back to your previous cart" before accessing the cart page.')
                );
                return $resultRedirect->setUrl($this->urlBuilder->getUrl('/'));
            }
            if ($subQuote && $subQuote->getQuoteStatus() == Data::SUB_QUOTE_WAITING) {
                $resultRedirect = $this->redirectFactory->create();
                $this->manager->addErrorMessage(
                    __('Please checkout your current waiting order or go
                    "Back to your previous cart" before accessing the cart page.')
                );
                return $resultRedirect->setUrl($this->urlBuilder->getUrl('/'));
            }
        }
        return $proceed();
    }
}
