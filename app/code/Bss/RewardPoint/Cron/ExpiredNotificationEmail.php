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
namespace Bss\RewardPoint\Cron;

use Bss\RewardPoint\Helper\Data;
use Bss\RewardPoint\Model\ResourceModel\NotificationFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime;
use Bss\RewardPoint\Model\ResourceModel\Customer;
use Bss\RewardPoint\Model\ResourceModel\TransactionFactory;

/**
 * Class Expired NotificationEmail
 *
 * Bss\RewardPoint\Cron
 */
class ExpiredNotificationEmail
{
    /**
     * @var Data
     */
    protected $bssHelper;

    /**
     * @var NotificationFactory
     */
    protected $notificationResource;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var TransactionFactory
     */
    protected $transactionResource;

    /**
     * @var Customer
     */
    protected $customerResource;

    /**
     * ExpiredNotificationEmail constructor.
     * @param Data $bssHelper
     * @param NotificationFactory $notificationResource
     * @param DateTime $dateTime
     * @param Customer $customerResource
     * @param TransactionFactory $transactionResource
     */
    public function __construct(
        Data $bssHelper,
        NotificationFactory $notificationResource,
        DateTime $dateTime,
        Customer $customerResource,
        TransactionFactory $transactionResource
    ) {
        $this->notificationResource = $notificationResource;
        $this->bssHelper = $bssHelper;
        $this->dateTime = $dateTime;
        $this->customerResource = $customerResource;
        $this->transactionResource = $transactionResource;
    }

    /**
     * Run cron send email to customer has expire points
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $now = time();
        foreach ($this->bssHelper->getAllWebsites() as $website) {
            $dayToSendMail = (int)$this->bssHelper->getValueConfig(
                Data::XML_PATH_EXPIRE_DAY_BEFORE,
                ScopeInterface::SCOPE_WEBSITE,
                $website->getId()
            );
            $dateCompareToSendMail = $now + $dayToSendMail * 86400;
            // format follow magento
            $dateCompareToSendMail = $this->dateTime->formatDate($dateCompareToSendMail, false);
            $expireInfo = $this->notificationResource->create()->getPointExpiredPerCustomer(
                $dateCompareToSendMail,
                $website->getId()
            );
            foreach ($expireInfo as $info) {
                $notification = $this->notificationResource->create()->getNotificationByCustomer($info['customer_id']);
                if (!empty($notification['notify_expiration'])) {
                    $customerName = $this->customerResource->getNameById($info['customer_id']);
                    $customerEmail = $this->customerResource->getEmailById($info['customer_id']);
                    $customerInfo = ['mail' => $customerEmail, 'name' => $customerName];
                    $balanceInfo = $this->transactionResource->create()->loadByCustomer(
                        $info['customer_id'],
                        $website->getId()
                    );
                    $this->bssHelper->sendExpiresEmail(
                        $info,
                        $customerInfo,
                        $balanceInfo['point_balance']
                    );
                }
            }
        }

        return $this;
    }
}
