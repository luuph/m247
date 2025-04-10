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

namespace Mageplaza\Affiliate\Observer;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\Affiliate\Helper\Data;
use Mageplaza\Affiliate\Model\Account;
use Mageplaza\Affiliate\Model\AccountFactory;

/**
 * Class CustomerAccountEdited
 * @package Mageplaza\Affiliate\Observer
 */
class CustomerSaveBefore implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * @var Account
     */
    protected $account;


    /**
     * @param Data $helper
     * @param CustomerFactory $customerFactory
     * @param AccountFactory $accountFactory
     * @param Account $account
     */
    public function __construct(
        Data $helper,
        CustomerFactory $customerFactory,
        AccountFactory $accountFactory,
        Account $account
    ) {
        $this->_helper = $helper;
        $this->customerFactory = $customerFactory;
        $this->accountFactory = $accountFactory;
        $this->account = $account;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $dataObject = $observer->getEvent()->getDataObject();
        $customerId = $dataObject->getId();

        $customOrigObject = $this->customerFactory->create()->load($customerId);
        $collection = $this->accountFactory->create()
            ->getCollection()
            ->addFieldToFilter('parent_email', $customOrigObject->getEmail());
        foreach ($collection->getData() as $accountData) {
            $account = $this->account->loadByCustomerId($accountData['customer_id']);
            $account->setParentEmail($dataObject->getEmail());
            $account->save();
        }
    }
}
