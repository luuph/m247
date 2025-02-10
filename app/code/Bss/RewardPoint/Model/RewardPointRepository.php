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
namespace Bss\RewardPoint\Model;

use Bss\RewardPoint\Block\Customer\RewardPoint;
use Bss\RewardPoint\Model\ResourceModel\Notification;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class RewardPointRepository implements \Bss\RewardPoint\Api\Data\RewardPointInterface
{
    /**
     * @var RewardPoint
     */
    protected $rewardPoint;

    /**
     * @var Notification
     */
    protected $notification;

    /**
     * @var \Bss\RewardPoint\Helper\InjectModel
     */
    protected $helperInject;

    /**
     * RewardPointRepository constructor.
     * @param RewardPoint $rewardPoint
     * @param Notification $notification
     * @param \Bss\RewardPoint\Helper\InjectModel $helperInject
     */
    public function __construct(
        RewardPoint $rewardPoint,
        Notification $notification,
        \Bss\RewardPoint\Helper\InjectModel $helperInject
    ) {
        $this->rewardPoint = $rewardPoint;
        $this->notification = $notification;
        $this->helperInject = $helperInject;
    }

    /**
     * Get Website id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getWebsiteId()
    {
        return $this->rewardPoint->getWebsiteId();
    }

    /**
     * Get Balance info
     *
     * @param CustomerInterface $customer
     * @return Transaction
     * @throws LocalizedException
     */
    protected function balanceInfo(CustomerInterface $customer)
    {
        $website = $customer->getWebsiteId();
        $customerId = $customer->getId();
        return $this->rewardPoint->getTransaction()->loadByCustomer($customerId, $website);
    }

    /**
     * Get point
     *
     * @param CustomerInterface $customer
     * @return float
     * @throws LocalizedException
     */
    public function getPoint($customer)
    {
        return $this->balanceInfo($customer)->getPointBalance();
    }

    /**
     * Get Point used
     *
     * @param CustomerInterface $customer
     * @return float
     * @throws LocalizedException
     */
    public function getPointUsed($customer)
    {
        return $this->balanceInfo($customer)->getPointSpent();
    }

    /**
     * Get point expired
     *
     * @param CustomerInterface $customer
     * @return float
     * @throws LocalizedException
     */
    public function getPointExpired($customer)
    {
        return $this->balanceInfo($customer)->getPointExpired();
    }

    /**
     * Get amount
     *
     * @param CustomerInterface $customer
     * @return float
     * @throws LocalizedException
     */
    public function getAmount($customer)
    {
        return $this->balanceInfo($customer)->getAmount();
    }

    /**
     * Get Notify balance
     *
     * @param CustomerInterface $customer
     * @return int|mixed
     * @throws LocalizedException
     */
    public function getNotifyBalance(CustomerInterface $customer)
    {
        $notify = $this->notification->getNotificationByCustomer($customer->getId());
        if (is_array($notify) && isset($notify['notify_balance'])) {
            return $notify['notify_balance'];
        }
        return 0;
    }

    /**
     * Get Notify expiration
     *
     * @param CustomerInterface $customer
     * @return int|mixed
     * @throws LocalizedException
     */
    public function getNotifyExpiration(CustomerInterface $customer)
    {
        $notify = $this->notification->getNotificationByCustomer($customer->getId());
        if (is_array($notify) && isset($notify['notify_expiration'])) {
            return $notify['notify_expiration'];
        }
        return '';
    }

    /**
     * Get rate point
     *
     * @param CustomerInterface $customer
     * @return false|float
     * @throws LocalizedException
     */
    public function getRatePoint(CustomerInterface $customer)
    {
        return $this->getRateCurrencytoPoint($customer);
    }

    /**
     * Get Rate currency to point
     *
     * @param CustomerInterface $customer
     * @return false|float
     * @throws LocalizedException
     */
    public function getRateCurrencytoPoint(CustomerInterface $customer)
    {
        $websiteId = $customer->getWebsiteId();
        $customerGroupId = $customer->getGroupId();

        $rate = $this->helperInject->createRateModel()->fetch(
            $customerGroupId,
            $websiteId
        );

        return $rate->getBasecurrencyToPointRate() ? round($rate->getBasecurrencyToPointRate()) : 1;
    }
}
