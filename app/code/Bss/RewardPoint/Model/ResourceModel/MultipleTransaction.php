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
namespace Bss\RewardPoint\Model\ResourceModel;

use Bss\RewardPoint\Helper\Data;
use Bss\RewardPoint\Helper\RewardCustomAction;
use Bss\RewardPoint\Model\ResourceModel\Transaction\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Class Multiple Transaction
 *
 * Bss\RewardPoint\Model\ResourceModel
 */
class MultipleTransaction
{
    /**
     * @var ResourceConnection
     */
    protected $resources;

    /**
     * @var TransactionFactory
     */
    protected $transactionResource;

    /**
     * @var Data
     */
    protected $bssHelper;

    /**
     * @var RewardCustomAction
     */
    protected $helperCustomAction;

    /**
     * @var CollectionFactory
     */
    protected $transactionColleciton;

    /**
     * @var \Bss\RewardPoint\Model\ResourceModel\NotificationFactory
     */
    protected $notificationResource;

    /**
     * MultipleTransaction constructor.
     * @param ResourceConnection $resources
     * @param TransactionFactory $transactionResource
     * @param Data $bssHelper
     * @param RewardCustomAction $helperCustomAction
     * @param CollectionFactory $collectionFactory
     * @param \Bss\RewardPoint\Model\ResourceModel\NotificationFactory $notificationResource
     */
    public function __construct(
        ResourceConnection $resources,
        TransactionFactory $transactionResource,
        Data $bssHelper,
        RewardCustomAction $helperCustomAction,
        CollectionFactory $collectionFactory,
        NotificationFactory $notificationResource
    ) {
        $this->transactionColleciton = $collectionFactory;
        $this->transactionResource = $transactionResource;
        $this->resources = $resources;
        $this->bssHelper = $bssHelper;
        $this->helperCustomAction = $helperCustomAction;
        $this->notificationResource = $notificationResource;
    }

    /**
     * Insert multiple transaction
     *
     * @param string $customer_ids
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function insertMultiple($customer_ids, $data)
    {
        $connection = $this->resources->getConnection();
        $transaction = $this->resources->getTableName('bss_reward_point_transaction');
        $customer_ids=($customer_ids == null) ? [] : explode(',', $customer_ids);
        $data_imp = [];
        $threshold = $this->bssHelper->getPointsMaximum($data['website_id']);
        foreach ($customer_ids as $customer_id) {
            $_data = $data;
            $_data['customer_id'] = $customer_id;
            $balanceInfo = $this->transactionResource->create()->loadByCustomer($customer_id, $_data['website_id']);
            $newBalance = $balanceInfo['point_balance'] + $_data['point'];
            if ($threshold > 0) {
                $_data['point'] = $newBalance >= $threshold ?
                    $threshold - $balanceInfo['point_balance'] : $_data['point'];
            }
            $data_imp[] = $_data;
        }
        if (!empty($data_imp)) {
            $connection->insertMultiple($transaction, $data_imp);
        }

        $lastIds = [];
        foreach ($customer_ids as $customer_id) {
            $lastIds[] = $this->transactionResource->create()->getLastTransactionIdsPerCustomer($customer_id);
        }
        $newTransactionCollection = $this->transactionColleciton->create();
        $newTransactionCollection->addFieldToFilter('transaction_id', ['in' => $lastIds]);
        foreach ($newTransactionCollection->getItems() as $item) {
            $notification = $this->notificationResource->create()->getNotificationByCustomer($item->getCustomerId());
            if (!empty($notification['notify_balance'])) {
                $customerId = $item->getCustomerId();
                $websiteId = $item->getWebsiteId();
                $balanceInfo = $this->transactionResource->create()->loadByCustomer($customerId, $websiteId);
                $customer = $this->helperCustomAction->getCustomer($customerId);

                $customerEmail = $customer->getEmail();
                $customerName = $customer->getName();
                $storeId = $customer->getData('store_id');

                $customerGroupId = $customer->getData('group_id');
                $rate = $this->helperCustomAction->getExchangeRate($customerGroupId, $websiteId);

                $customerInfo = ['mail' => $customerEmail, 'name' => $customerName, 'store_id' => $storeId];

                $this->bssHelper->sendNotiEmail(
                    $item,
                    $balanceInfo['point_balance'],
                    $customerInfo,
                    $rate
                );
            }
        }
    }
}
