<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Block\Account;

use Exception;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Mageplaza\Affiliate\Block\Account;
use Mageplaza\Affiliate\Helper\Data as AffiliateHelper;
use Mageplaza\Affiliate\Helper\Payment;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\CampaignFactory;
use Mageplaza\Affiliate\Model\ResourceModel\Account\CollectionFactory as AccountCollectionFactory;
use Mageplaza\Affiliate\Model\TransactionFactory;
use Mageplaza\Affiliate\Model\WithdrawFactory;
use Mageplaza\Affiliate\Model\WithdrawhistoryFactory;
use Zend_Db_Select_Exception;

/**
 * Class History
 * @package Mageplaza\Affiliate\Block\Account
 */
class History extends Account
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var QuoteCollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerMetadataInterface
     */
    protected $customerMetadataService;

    /**
     * @var AccountCollectionFactory
     */
    protected $accountCollectionFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Account constructor.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param View $helperView
     * @param AffiliateHelper $affiliateHelper
     * @param Payment $paymentHelper
     * @param JsonHelper $jsonHelper
     * @param Registry $registry
     * @param PriceHelper $pricingHelper
     * @param ObjectManagerInterface $objectManager
     * @param CampaignFactory $campaignFactory
     * @param AccountFactory $accountFactory
     * @param WithdrawFactory $withdrawFactory
     * @param TransactionFactory $transactionFactory
     * @param ResourceConnection $resourceConnection
     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerMetadataInterface $customerMetadataService
     * @param AccountCollectionFactory $accountCollectionFactory
     * @param Escaper $escaper
     * @param CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        View $helperView,
        AffiliateHelper $affiliateHelper,
        Payment $paymentHelper,
        JsonHelper $jsonHelper,
        Registry $registry,
        PriceHelper $pricingHelper,
        ObjectManagerInterface $objectManager,
        CampaignFactory $campaignFactory,
        AccountFactory $accountFactory,
        WithdrawFactory $withdrawFactory,
        TransactionFactory $transactionFactory,
        ResourceConnection $resourceConnection,
        QuoteCollectionFactory $quoteCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerMetadataInterface $customerMetadataService,
        AccountCollectionFactory $accountCollectionFactory,
        Escaper $escaper,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->resourceConnection       = $resourceConnection;
        $this->quoteCollectionFactory   = $quoteCollectionFactory;
        $this->customerRepository       = $customerRepository;
        $this->customerMetadataService  = $customerMetadataService;
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->escaper                  = $escaper;
        $this->customerFactory    = $customerFactory;
        parent::__construct(
            $context,
            $customerSession,
            $helperView,
            $affiliateHelper,
            $paymentHelper,
            $jsonHelper,
            $registry,
            $pricingHelper,
            $objectManager,
            $campaignFactory,
            $accountFactory,
            $withdrawFactory,
            $transactionFactory,
            $customerFactory,
            $data
        );
    }

    /**
     * @return mixed
     * @throws LocalizedException
     * @throws Zend_Db_Select_Exception
     * @throws Exception
     */
    public function getReferHistory()
    {
        $quoteIds    = [];
        $accountId   = $this->getCurrentAccount()->getId();
        $accountCode = $this->getCurrentAccount()->getCode();
        $collection  = $this->quoteCollectionFactory->create();
        $quoteTable  = $collection->getMainTable();
        $connection  = $this->resourceConnection->getConnection();

        $referSelect = clone $connection->select();
        $referSelect->from($quoteTable, ['entity_id'])
            ->where('customer_id IS NOT NULL')
            ->where(
                "affiliate_key IN (?) OR affiliate_key LIKE '{$accountCode}-%'",
                [$accountId, $accountCode]
            )
            ->group('customer_id');

        $guestReferSelect = clone $connection->select();
        $guestReferSelect->from($quoteTable, ['entity_id'])
            ->where('customer_id IS NULL')
            ->where('customer_email IS NOT NULL')
            ->where(
                "affiliate_key IN (?) OR (affiliate_key LIKE '{$accountCode}-%' AND is_active = 0)",
                [$accountId, $accountCode]
            );

        $customerIds     = $this->getCustomersByParentId($accountId);
        $affiliateSelect = clone $connection->select();
        $affiliateSelect->from($quoteTable, ['entity_id'])
            ->where('customer_id IN (?)', $customerIds)
            ->group('customer_id');

        $unionSelect = clone $connection->select();
        $unionSelect->union([$referSelect, $guestReferSelect, $affiliateSelect])->distinct();

        foreach ($connection->fetchAll($unionSelect) as $item) {
            $quoteIds[] = $item['entity_id'];
        }

        $collection->addFieldToFilter('entity_id', ['in' => $quoteIds])
            ->setOrder('created_at', 'DESC');

        if ($collection->getSize()) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'affiliate.transaction.pager');
            // assign collection to pager
            $limit = $this->_request->getParam('limit') ? : 10;
            $pager->setLimit($limit)->setCollection($collection);
            $this->setChild('pager', $pager);// set pager block in layout

            $currentPage = $pager->getCurrentPage();
            $lastPage    = $pager->getLastPageNum();
            $offset      = !$pager->isLastPage()
                ? ($pager->getTotalNum() % $limit !== 0 ? $limit - ($pager->getTotalNum() % $limit) : 0)
                : 0;
            $count       = (int) ($limit * ($lastPage - $currentPage) + $collection->count() - $offset);

            foreach ($collection as $item) {
                $item->setPagerId($count);
                $count--;
            }
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param  Quote $quote
     * @return string
     */
    public function getCustomerName(Quote $quote)
    {
        $name = '';
        try {
            if ($quote->getCustomerId()) {
                $customer = $this->customerRepository->getById($quote->getCustomerId());
                $name     = $this->_helperView->getCustomerName($customer);
            } else {
                $name     = $this->getGuestCustomerName($quote);
            }
        } catch (NoSuchEntityException|LocalizedException $e) {
            return $name;
        }
        return $name;
    }

    /**
     * @param  Quote $quote
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getGuestCustomerName(Quote $quote)
    {
        $name           = '';
        $billingAddress = $quote->getBillingAddress();
        $prefix         = $billingAddress->getPrefix() ? : $quote->getCustomerPrefix();
        $suffix         = $billingAddress->getSuffix() ? : $quote->getCustomerSuffix();
        $firstName      = $billingAddress->getFirstname() ? : $quote->getCustomerFirstname();
        $middleName     = $billingAddress->getMiddlename() ? : $quote->getCustomerMiddlename();
        $lastName       = $billingAddress->getLastname() ? : $quote->getCustomerLastname();
        $prefixMetadata = $this->customerMetadataService->getAttributeMetadata('prefix');
        if ($prefixMetadata->isVisible() && $prefix) {
            $name .= $prefix . ' ';
        }

        $name .= $firstName;

        $middleNameMetadata = $this->customerMetadataService->getAttributeMetadata('middlename');
        if ($middleNameMetadata->isVisible() && $middleName) {
            $name .= ' ' . $middleName;
        }

        $name .= ' ' . $lastName;

        $suffixMetadata = $this->customerMetadataService->getAttributeMetadata('suffix');
        if ($suffixMetadata->isVisible() && $suffix) {
            $name .= ' ' . $suffix;
        }

        if (trim($name) === '') {
            $name = (string)__('Guest');
        }

        return $this->escaper->escapeHtml($name);
    }

    /**
     * @param int $parentId
     * @return array
     */
    public function getCustomersByParentId($parentId)
    {
        $customerIds = [];
        $accountCollection = $this->accountCollectionFactory->create()
            ->addFieldToFilter('parent', ['eq' => $parentId]);

        foreach ($accountCollection as $account) {
            $customerIds[] = $account->getCustomerId();
        }

        return $customerIds;
    }
}
