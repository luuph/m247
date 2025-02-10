<?php
declare(strict_types = 1);

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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Controller\Order;

use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Quote\Model\QuoteFactory;
use Bss\CompanyAccount\Model\Checkout as CheckoutModel;
use Magento\Checkout\Model\Session as CheckoutSession;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;

/**
 * Class Checkout
 *
 * @package Bss\CompanyAccount\Controller\Order
 */
class Checkout extends \Magento\Checkout\Controller\Onepage
{
    /**
     * @var Onepage
     */
    protected $onePage;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var CheckoutModel
     */
    protected $checkoutModel;

    /**
     * @var SubUserQuoteRepositoryInterface
     */
    protected $subQuoteRepository;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * @param PermissionsChecker $permissionsChecker
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Checkout\Model\Type\Onepage $onePage
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param CheckoutModel $checkoutModel
     * @param SubUserQuoteRepositoryInterface $subQuoteRepository
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        PermissionsChecker                                 $permissionsChecker,
        \Magento\Quote\Model\QuoteFactory                  $quoteFactory,
        \Magento\Checkout\Model\Type\Onepage               $onePage,
        \Magento\Framework\App\Action\Context              $context,
        \Magento\Customer\Model\Session                    $customerSession,
        CustomerRepositoryInterface                        $customerRepository,
        AccountManagementInterface                         $accountManagement,
        \Magento\Framework\Registry                        $coreRegistry,
        \Magento\Framework\Translate\InlineInterface       $translateInline,
        \Magento\Framework\Data\Form\FormKey\Validator     $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory              $layoutFactory,
        \Magento\Quote\Api\CartRepositoryInterface         $quoteRepository,
        \Magento\Framework\View\Result\PageFactory         $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory       $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory    $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory   $resultJsonFactory,
        CheckoutModel                                      $checkoutModel,
        SubUserQuoteRepositoryInterface $subQuoteRepository,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        CheckoutSession $checkoutSession
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement,
            $coreRegistry,
            $translateInline,
            $formKeyValidator,
            $scopeConfig,
            $layoutFactory,
            $quoteRepository,
            $resultPageFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $resultJsonFactory
        );
        $this->onePage = $onePage;
        $this->checkoutModel = $checkoutModel;
        $this->subQuoteRepository = $subQuoteRepository;
        $this->redirectFactory = $redirectFactory;
        $this->quoteFactory = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * Function checkout execute
     *
     * @return ResultInterface|Page
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $quoteId = (int)$this->getRequest()->getParam('order_id');
        $subQuote = $this->subQuoteRepository->getByQuoteId($quoteId);
        $quote = $this->quoteRepository->get($quoteId);
        if ($quote->getReservedOrderId()) {
            $quote->setIsActive(false);
            $this->quoteRepository->save($quote);
            $this->messageManager->addErrorMessage(__('This order request is already placed by Order Id %1', $quote->getReservedOrderId()));
            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setUrl('/');
            return $resultRedirect;
        }
        $deniedPlaceOrder = $this->permissionsChecker->isDenied(Permissions::PLACE_ORDER);
        if ($subQuote && ($subQuote->getQuoteStatus() === Data::SUB_QUOTE_REJECT ||
            $deniedPlaceOrder && $subQuote->getQuoteStatus() === Data::SUB_QUOTE_WAITING)) {
            if ($previousQuote = $this->checkoutSession->getQuoteId()) {
                $resultRedirect = $this->redirectFactory->create();
                $resultRedirect->setPath('companyaccount/order/checkout', ['order_id' => $previousQuote]);
                return $resultRedirect;
            }
        }
        $this->checkoutModel->replaceQuote($quoteId);
        $this->onePage->initCheckout();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Checkout'));
        $resultPage->getLayout()->getBlock('checkout.root');
        return $resultPage;
    }
}
