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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Plugin\Quote;

use Bss\CompanyAccount\Model\Checkout;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Magento\Checkout\Model\Type\Onepage;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Bss\CompanyAccount\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;

class GetActiveQuote
{
    /**
     * @var Checkout
     */
    private $checkout;

    /**
     * @var PermissionsChecker
     */
    private $permissionsChecker;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CustomerSessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var SubUserQuoteRepositoryInterface
     */
    private $subUserQuoteRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @param PermissionsChecker $permissionsChecker
     * @param CartRepositoryInterface $quoteRepository
     * @param CustomerSessionFactory $customerSessionFactory
     * @param Checkout $checkout
     * @param LoggerInterface $logger
     * @param SubUserQuoteRepositoryInterface $subUserQuoteRepository
     * @param CheckoutSession $checkoutSession
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        PermissionsChecker              $permissionsChecker,
        CartRepositoryInterface         $quoteRepository,
        CustomerSessionFactory          $customerSessionFactory,
        Checkout                        $checkout,
        LoggerInterface                 $logger,
        SubUserQuoteRepositoryInterface $subUserQuoteRepository,
        CheckoutSession                 $checkoutSession,
        RedirectFactory                 $redirectFactory
    ) {
        $this->permissionsChecker = $permissionsChecker;
        $this->quoteRepository = $quoteRepository;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->checkout = $checkout;
        $this->subUserQuoteRepository = $subUserQuoteRepository;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Set Quote for SubUser and Replace Quote
     *
     * @param object $subject
     * @return mixed|void
     */
    public function beforeGetActiveQuote($subject)
    {
        $params = $subject->getRequest()->getParams();
        if (isset($params['companyaccount']) && $params['companyaccount'] === '1') {
            $quoteId = $params['order_id'];
            try {
                $approveQuote = $this->quoteRepository->get($quoteId);
                $customerSession = $this->customerSessionFactory->create();
                $customerID = $customerSession->getCustomerId();
                $subUser = $customerSession->getSubUser();
                if ($approveQuote->getData('bss_is_sub_quote') == $customerID) {
                    if (($subUser && !$this->permissionsChecker->isDenied(Permissions::PLACE_ORDER))
                        || $this->permissionsChecker->isAdmin()) {
                        $this->checkout->replaceQuote($quoteId);
                    } elseif (!$this->permissionsChecker->isDenied(Permissions::PLACE_ORDER_WAITING)
                        && $this->canCheckOut($quoteId)) {
                        $this->checkout->replaceQuote($quoteId);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }

    /**
     * Quote can Checkout
     *
     * @param int $quoteId
     * @return bool
     */
    public function canCheckOut($quoteId)
    {
        return $this->subUserQuoteRepository->getByQuoteId($quoteId)->getQuoteStatus() == 'approved';
    }

    /**
     * Check quote rejected function
     *
     * @param mixed $subject
     * @param mixed $result
     * @return void
     */
    public function afterExecute($subject, $result) {
        $isCompanyAccount = $subject->getRequest()->getParam('companyaccount');
        if($isCompanyAccount && $isCompanyAccount == '1') {
            $quoteId = (int)$subject->getRequest()->getParam('order_id');
            $subQuote = $this->subUserQuoteRepository->getByQuoteId($quoteId);
            $deniedPlaceOrder = $this->permissionsChecker->isDenied(Permissions::PLACE_ORDER);
            if ($subQuote && ($subQuote->getQuoteStatus() === Data::SUB_QUOTE_REJECT ||
            $deniedPlaceOrder && $subQuote->getQuoteStatus() === Data::SUB_QUOTE_WAITING)) {
                if ($previousQuote = $this->checkoutSession->getQuoteId()) {
                    $resultRedirect = $this->redirectFactory->create();
                    $resultRedirect->setPath($this->verifyPath($subject->getRequest()->getPathInfo()), ['_query' => ['companyaccount' => '1', 'order_id' => $previousQuote]]);
                    return $resultRedirect;
                }
            }
            return $result;
        }
        return $result;
    }

    /**
     * Remove /
     *
     * @param string $path
     * @return void
     */
    private function verifyPath($path)
    {
        return str_replace("/", "", $path ?? '');
    }
}
