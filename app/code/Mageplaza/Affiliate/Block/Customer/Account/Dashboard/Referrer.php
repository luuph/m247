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

namespace Mageplaza\Affiliate\Block\Customer\Account\Dashboard;

use Magento\Customer\Helper\View;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Affiliate\Block\Account;
use Mageplaza\Affiliate\Helper\Data as AffiliateHelper;
use Mageplaza\Affiliate\Helper\Payment;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\CampaignFactory;
use Mageplaza\Affiliate\Model\TransactionFactory;
use Mageplaza\Affiliate\Model\WithdrawFactory;

/**
 * Class Referrer
 * @package Mageplaza\Affiliate\Block\Customer\Account\Dashboard
 */
class Referrer extends Account
{
    /**
     * @var null
     */
    protected $_parentAccount = null;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

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
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->customerFactory = $customerFactory;

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
     * @return \Mageplaza\Affiliate\Model\Account
     */
    public function getParentAccount()
    {
        if ($this->_parentAccount === null) {
            $currentAccount       = $this->getCurrentAccount();
            $this->_parentAccount = $this->_affiliateHelper->getAffiliateAccount($currentAccount->getParent());
        }

        return $this->_parentAccount;
    }

    /**
     * @param \Mageplaza\Affiliate\Model\Account $account
     * @return string
     */
    public function getParentName($account)
    {
        $customer = $this->customerFactory->create()->load($account->getCustomerId());

        try {
            return $customer->getName();
        } catch (LocalizedException $e) {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getParentEmail()
    {
        return $this->getParentAccount()->getCustomer()->getEmail();
    }
}
