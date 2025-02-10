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
namespace Bss\RewardPoint\Plugin\Model;

use Bss\RewardPoint\Helper\Data;
use Bss\RewardPoint\Helper\RewardCustomAction;
use Bss\RewardPoint\Model\TransactionFactory;
use Bss\RewardPoint\Model\ResourceModel\NotificationFactory;
use Bss\RewardPoint\Model\Config\Source\TransactionActions;

/**
 * Class transaction
 *
 * Bss\RewardPoint\Plugin\Model
 */
class Transaction
{
    /**
     * @var Data
     */
    protected $bssHelper;

    /**
     * @var RewardCustomAction
     */
    protected $helperCustomAction;

    /**
     * @var NotificationFactory
     */
    protected $notificationResource;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * Transaction constructor.
     * @param Data $bssHelper
     * @param RewardCustomAction $helperCustomAction
     * @param TransactionFactory $transactionFactory
     * @param NotificationFactory $notificationResource
     */
    public function __construct(
        Data $bssHelper,
        RewardCustomAction $helperCustomAction,
        TransactionFactory $transactionFactory,
        NotificationFactory $notificationResource
    ) {
        $this->bssHelper = $bssHelper;
        $this->helperCustomAction = $helperCustomAction;
        $this->transactionFactory = $transactionFactory;
        $this->notificationResource = $notificationResource;
    }

    /**
     * Set maximum balance per customer
     *
     * @param \Bss\RewardPoint\Model\Transaction $subject
     * @return \Bss\RewardPoint\Model\Transaction
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave(\Bss\RewardPoint\Model\Transaction $subject)
    {
        $customerId = $subject->getCustomerId();
        $websiteId = $subject->getWebsiteId();
        $threshold = (int)$this->bssHelper->getPointsMaximum($websiteId);
        if ($threshold > 0) {
            $transaction = $this->transactionFactory->create();
            $currentPointBalance = $transaction->loadByCustomer($customerId, $websiteId)->getPointBalance();
            $newBalance = $currentPointBalance + $subject->getPoint();
            $point = $newBalance >= $threshold ? $threshold - $currentPointBalance : $subject->getPoint();
            $subject->setPoint($point);
        }
        return $subject;
    }
    /**
     * Send email for reward point action
     *
     * @param \Bss\RewardPoint\Model\Transaction $subject
     * @param \Bss\RewardPoint\Model\Transaction $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterSave(\Bss\RewardPoint\Model\Transaction $subject, $result)
    {
        $customerId = $subject->getCustomerId();
        $notification = $this->notificationResource->create()->getNotificationByCustomer($customerId);
        if (!empty($notification['notify_balance']) || $subject->getAction() == TransactionActions::REGISTRATION) {
            $transaction = $this->transactionFactory->create();
            $websiteId = $subject->getWebsiteId();
            $pointBalance = $transaction->loadByCustomer($customerId, $websiteId)->getPointBalance();

            $customer = $this->helperCustomAction->getCustomer($customerId);

            $customerEmail = $customer->getEmail();
            $customerName = $customer->getName();
            $storeId = $customer->getData('store_id');

            $customerGroupId = $customer->getData('group_id');
            $rate = $this->helperCustomAction->getExchangeRate($customerGroupId, $websiteId);

            $customerInfo = ['mail' => $customerEmail, 'name' => $customerName, 'store_id' => $storeId];
            $this->bssHelper->sendNotiEmail(
                $subject,
                $pointBalance,
                $customerInfo,
                $rate
            );
        }

        return $result;
    }
}
