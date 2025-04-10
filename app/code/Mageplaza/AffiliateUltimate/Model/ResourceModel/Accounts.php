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
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliateUltimate\Model\ResourceModel;

use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Mageplaza\Affiliate\Helper\Data;
use Mageplaza\Affiliate\Model\ResourceModel\Account;

/**
 * Class Accounts
 * @package Mageplaza\AffiliateUltimate\Model\ResourceModel
 */
class Accounts extends Account
{
    /**
     * @var AccountManagementInterface
     */
    protected $customerManagement;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagementInterface;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * Accounts constructor.
     *
     * @param Data $helper
     * @param Context $context
     * @param AccountManagementInterface $accountManagement
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param GroupManagementInterface $groupManagementInterface
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     */
    public function __construct(
        Data $helper,
        Context $context,
        AccountManagementInterface $accountManagement,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        GroupManagementInterface $groupManagementInterface,
        CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->_helper = $helper;
        $this->customerManagement = $accountManagement;
        $this->customerDataFactory = $customerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->groupManagementInterface = $groupManagementInterface;
        $this->customerRepositoryInterface = $customerRepositoryInterface;

        parent::__construct($helper, $context);
    }

    /**
     * @param $data
     *
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function createCustomer($data)
    {
        $customer = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customer,
            [
                'email' => $data['email'],
                'website_id' => $data['website_id'],
                'firstname' => 'firstname',
                'lastname' => 'lastname',
                'group' => $this->groupManagementInterface->getDefaultGroup($data['store_id']),
                'sendemail_store_id' => $data['store_id']
            ],
            CustomerInterface::class
        );

        return $this->customerManagement->createAccount($customer);
    }

    /**
     * @param array $entityData
     *
     * @return $this
     * @throws Exception
     */
    public function saveEntityFinish(array $entityData)
    {
        $this->beginTransaction();
        try {
            foreach ($entityData as $key => $data) {
                $customer = $this->customerRepositoryInterface->get($data['email']);
                if (!$customer->getId()) {
                    $customer = $this->createCustomer($data);
                }

                $entityData[$key]['customer_id'] = $customer->getId();
            }

            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }

        return $this;
    }
}
