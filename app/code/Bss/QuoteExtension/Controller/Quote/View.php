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
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Controller\Quote;

use Bss\QuoteExtension\Helper\Customer\AutoLogging;
use Bss\QuoteExtension\Helper\Data;
use Bss\QuoteExtension\Model\ManageQuote;
use Bss\QuoteExtension\Model\QuoteCustomerGroupId;
use Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory as QuoteExtensionCollection;
use Bss\QuoteExtension\Api\ManageQuoteRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Html\Links;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class View
 *
 * @package Bss\QuoteExtension\Controller\Quote
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends Action
{
    /**
     * @var QuoteCustomerGroupId
     */
    protected $quoteCustomerGroupId;

    /**
     * @var QuoteExtensionCollection
     */
    protected $quoteExtensionCollection;
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var QuoteFactory
     */
    protected $magentoQuoteFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var AutoLogging
     */
    protected $bssHelperLogging;

    /**
     * @var ManageQuoteRepositoryInterface
     */
    protected $qeRepository;

    /**
     * @var CollectionFactory
     */
    protected $salesOrderCollectionFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * View constructor.
     *
     * @param QuoteCustomerGroupId $quoteCustomerGroupId
     * @param QuoteExtensionCollection $quoteExtensionCollection
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param QuoteFactory $magentoQuoteFactory
     * @param Data $helper
     * @param CheckoutSession $checkoutSession
     * @param AutoLogging $bssHelperLogging
     * @param ManageQuoteRepositoryInterface $qeRepository
     * @param Session $customerSession
     * @param CollectionFactory $salesOrderCollectionFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        QuoteCustomerGroupId     $quoteCustomerGroupId,
        QuoteExtensionCollection $quoteExtensionCollection,
        Context                  $context,
        PageFactory              $resultPageFactory,
        Registry                 $registry,
        QuoteFactory             $magentoQuoteFactory,
        Data                     $helper,
        CheckoutSession          $checkoutSession,
        AutoLogging              $bssHelperLogging,
        ManageQuoteRepositoryInterface $qeRepository,
        Session                  $customerSession,
        CollectionFactory $salesOrderCollectionFactory
    ) {
        $this->quoteCustomerGroupId = $quoteCustomerGroupId;
        $this->quoteExtensionCollection = $quoteExtensionCollection;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->magentoQuoteFactory = $magentoQuoteFactory;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->bssHelperLogging = $bssHelperLogging;
        $this->qeRepository = $qeRepository;
        $this->customerSession = $customerSession;
        $this->salesOrderCollectionFactory = $salesOrderCollectionFactory;
    }

    /**
     * Dispatch Controller
     *
     * @param RequestInterface $request
     * @return ResponseInterface|Redirect
     * @throws NotFoundException|\Magento\Framework\Exception\SessionException
     */
    public function dispatch(RequestInterface $request)
    {
        $params = $this->_request->getParams();
        $requestQuote = $this->qeRepository->getById($params['quote_id']);
        if (!$requestQuote->getEntityId()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath($this->_url->getUrl('noroute'));
        }
        $token = $requestQuote->getToken();
        if (isset($params['token']) && $token == $params['token'] && !$this->customerSession->isLoggedIn()) {
            if ($requestQuote->getEntityId()) {
                if ($requestQuote->getCustomerId()) {
                    $quote = $this->magentoQuoteFactory->create()->load($requestQuote->getQuoteId());
                    $isAutoLogging = $this->bssHelperLogging->isAutoLogging();
                    if ($isAutoLogging) {
                        $this->bssHelperLogging->setCustomerDataLoggin($quote);
                        return parent::dispatch($request);
                    } else {
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $this->messageManager->addErrorMessage(__('You have no permission to see that quote.'));
                        return $resultRedirect->setPath($this->_url->getUrl('/'));
                    }
                } else {
                    return parent::dispatch($request);
                }
            } else {
                $this->_actionFlag->set('', 'no-dispatch', true);
            }
        }
        if (!isset($params['token']) && !$this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('You have no permission to see that quote.'));
            return $resultRedirect->setPath($this->_url->getUrl('/'));
        }
        if (isset($params['quote_id']) && $this->customerSession->isLoggedIn()) {
            if ($requestQuote->getEntityId()) {
                if ($requestQuote->getCustomerId() != $this->customerSession->getCustomerId()) {
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $this->messageManager->addErrorMessage(__('You have no permission to see that quote.'));
                    return $resultRedirect->setPath($this->_url->getUrl('/'));
                }
            } else {
                $this->_actionFlag->set('', 'no-dispatch', true);
            }
        }
        return parent::dispatch($request);
    }

    /**
     * Quote View Page
     *
     * @return Redirect|ResultInterface|Page
     * @throws LocalizedException
     */
    public function execute()
    {
        $enable = $this->helper->isEnable();
        $quoteId = $this->getRequest()->getParam('quote_id');
        $token = $this->getRequest()->getParam('token');
        $quote = $this->quoteExtensionCollection->create()
            ->addFieldToFilter('main_table.entity_id', $quoteId)->getLastItem();
        $subUserId = $quote->getSubUserId();
        $this->coreRegistry->register('sub_user_id_quote', $subUserId);
        $magentoQuote = $this->magentoQuoteFactory->create()->load($quote->getQuoteId());
        $order = $this->getOrder($quote);
        $magentoQuote = $this->quoteCustomerGroupId->getQuoteView($magentoQuote, $magentoQuote->getCustomerId(), $quote);
        if (!$this->checkPermissionSubUser($subUserId)
            || (!isset($token) && !$this->checkCustomerViewQuote($quote->getCustomerId()))) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('You have no permission to see that quote.'));
            return $resultRedirect->setPath('quoteextension/quote/history');
        }
        if ($enable && $quoteId && $quote->getEntityId() && $magentoQuote->getId()) {
            $resultPage = $this->resultPageFactory->create();
            $this->coreRegistry->register('current_quote_extension', $quote);
            $this->coreRegistry->register('current_quote', $magentoQuote);
            $this->coreRegistry->register('order', $order);
            $resultPage->getConfig()->getTitle()->set(__('Quote # %1', $quote->getIncrementId()));

            /** @var Links $navigationBlock */
            $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('quoteextension/quote/history');
            }

            $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
            if ($block) {
                $block->setRefererUrl($this->_redirect->getRefererUrl());
            }
            $this->checkoutSession->setIsQuoteExtension($magentoQuote->getId());
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('The request quote id no longer exists.'));
            return $resultRedirect->setPath('quoteextension/quote/history');
        }
    }

    /**
     * Check permission sub user with quote id
     *
     * @param string $subUserIdQuote
     * @return bool
     * @throws LocalizedException
     */
    public function checkPermissionSubUser($subUserIdQuote)
    {
        if ($this->helper->isEnableCompanyAccount()) {
            $subUserIdCurrent = $this->getRequest()->getParam("sub_user_id_current");
            $viewAllQuotes = $this->getRequest()->getParam("allow_view_all_quotes");
            if ($subUserIdCurrent && !$viewAllQuotes && $subUserIdQuote != $subUserIdCurrent) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check customer view quote id
     *
     * @param string $customerIdQuote
     * @return bool
     */
    public function checkCustomerViewQuote($customerIdQuote)
    {
        $customerIdCurrent = $this->helper->getCustomerIdCurrent();
        if ($customerIdCurrent != $customerIdQuote) {
            return false;
        }
        return true;
    }

    /**
     * Get order by quote
     *
     * @param ManageQuote $quoteExtension
     * @return mixed
     */
    public function getOrder($quoteExtension)
    {
        return  $this->salesOrderCollectionFactory->create()->addFieldToFilter(
            'quote_id',
            ['in' => [$quoteExtension->getTargetQuote(), $quoteExtension->getBackendQuoteId()]]
        )->getFirstItem();
    }
}
