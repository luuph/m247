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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Block;

/**
 * Class Reward Point
 *
 * Bss\RewardPoint\Block
 */
class RewardPoint extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Bss\RewardPoint\Model\Config\Source\TransactionActions
     */
    protected $transactionActions;

    /**
     * @var \Bss\RewardPoint\Helper\InjectModel
     */
    protected $helperInject;

    /**
     * RewardPoint constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Bss\RewardPoint\Model\Config\Source\TransactionActions $transactionActions
     * @param \Bss\RewardPoint\Helper\InjectModel $helperInject
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Bss\RewardPoint\Model\Config\Source\TransactionActions $transactionActions,
        \Bss\RewardPoint\Helper\InjectModel $helperInject,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        $this->transactionActions = $transactionActions;
        $this->helperInject = $helperInject;
        parent::__construct($context, $data);
    }

    /**
     * Get transaction collection
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTransactionCollection()
    {
        $websiteId = $this->getWebsiteId();
        $customerId = $this->getCustomerId();
        $collection = $this->helperInject->createTransactionCollection()->addFieldToFilter('customer_id', $customerId);
        $collection->addFieldToFilter('website_id', $websiteId)->setOrder('transaction_id', 'desc');
        return $collection;
    }

    /**
     * Get balance info
     *
     * @return \Bss\RewardPoint\Model\Transaction
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBalanceInfo()
    {
        $websiteId = $this->getWebsiteId();
        $customerId = $this->getCustomerId();

        return $this->getTransaction()->loadByCustomer($customerId, $websiteId);
    }

    /**
     * Get rate currency to point
     *
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRateCurrencytoPoint()
    {
        $websiteId = $this->getWebsiteId();
        $customerGroupId = $this->getCustomerGroupId();

        $rate = $this->helperInject->createRateModel()->fetch(
            $customerGroupId,
            $websiteId
        );
        return $rate->getBasecurrencyToPointRate() ? round($rate->getBasecurrencyToPointRate()) : 1;
    }

    /**
     * Get currency
     *
     * @return float|string
     */
    public function getCurrency()
    {
        return $this->priceHelper->currency(1, true, false);
    }

    /**
     * Get transaction
     *
     * @return \Bss\RewardPoint\Model\Transaction
     */
    public function getTransaction()
    {
        return $this->helperInject->createTransactionModel();
    }

    /**
     * Get website id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * Get customer
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->customerSession->create()->getCustomer();
    }

    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getCustomer()->getId();
    }

    /**
     * Get customer group id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->getCustomer()->getGroupId();
    }

    /**
     * Get actions name
     *
     * @param string $action
     * @return string
     */
    public function getActionsName($action)
    {
        $actions_name = $this->transactionActions->toArray();
        return isset($actions_name[$action]) ? $actions_name[$action] : '';
    }
}
