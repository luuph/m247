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
 * @copyright  Copyright (c) 2019-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Model;

use Bss\RewardPoint\Helper\Data;
use Bss\RewardPoint\Helper\InjectModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Transaction extends AbstractModel
{
    /**
     * @var \Bss\RewardPoint\Helper\Data
     */
    protected $rewardHelper;

    /**
     * @var \Bss\RewardPoint\Helper\InjectModel
     */
    protected $helperInject;

    /**
     * Transaction constructor.
     *
     * @param Data $rewardHelper
     * @param InjectModel $helperInject
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Bss\RewardPoint\Helper\Data $rewardHelper,
        \Bss\RewardPoint\Helper\InjectModel $helperInject,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->rewardHelper = $rewardHelper;
        $this->helperInject = $helperInject;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\RewardPoint\Model\ResourceModel\Transaction::class);
    }

    /**
     * Load by customer
     *
     * @param int $customerId
     * @param int $websiteId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByCustomer($customerId, $websiteId)
    {
        $data = $this->_getResource()->loadByCustomer($customerId, $websiteId);
        return $this->setData($data);
    }

    /**
     * Get point balance review
     *
     * @param array $bind
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPointBalanceReview($bind)
    {
        return $this->_getResource()->getPointBalanceReview($bind);
    }

    /**
     * Update point used
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updatePointUsed()
    {
        return $this->_getResource()->updatePointUsed($this);
    }

    /**
     * Update point expired
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updatePointExpired()
    {
        return $this->_getResource()->updatePointExpired();
    }

    /**
     * @param $orderId
     * @return mixed
     * @throws LocalizedException
     */
    public function checkOrderAddPoint($orderId)
    {
        return $this->_getResource()->checkOrderAddPoint($orderId);
    }

    /**
     * Get expired at
     *
     * @return bool|false|float|int|string|null
     */
    public function getExpiresAt()
    {
        $expriesAt = (int)$this->getData('expires_at');
        $createdAt = $this->getData('created_at');
        return $this->rewardHelper->convertExpiredDayToDate($expriesAt, $createdAt);
    }

    /**
     * Set expired at
     *
     * @param int|null $expiredAt
     * @return $this
     * @throws LocalizedException
     */
    public function setExpiresAt($expiredAt)
    {
        if ($expiredAt && is_numeric($expiredAt)) {
            $this->setData('expires_at', $expiredAt);
            return $this;
        }
        throw new LocalizedException(__('expires_at must be number type.'));
    }

    /**
     * Get balance by transaction_id.
     *
     * @param string $transactionId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBalanceByTransaction($transactionId)
    {
        return $this->helperInject->createTransactionResource()->getPointBalanceForGrid(
            $transactionId
        );
    }
}
