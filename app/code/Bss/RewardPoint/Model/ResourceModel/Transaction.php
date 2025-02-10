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
use Bss\RewardPoint\Model\Config\Source\TransactionActions;
use Magento\Framework\DB\Sql\ExpressionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class transaction
 *
 * Bss\RewardPoint\Model\ResourceModel
 */
class Transaction extends AbstractDb
{
    /**
     * @var int
     */
    protected $insertedRows = 0;

    /**
     * @var int
     */
    protected $invalidDataRows = 0;

    /**
     * @var string
     */
    protected $emptyRequiredDataRows = "";

    /**
     * @var string
     */
    protected $wrongWebsiteCode = "";

    /**
     * @var string
     */
    protected $customerNotExist = "";

    /**
     * @var string
     */
    protected $invalidDateRows = "";

    /**
     * @var \Bss\RewardPoint\Helper\Data
     */
    protected $bssHelper;

    /**
     * @var TransactionActions
     */
    protected $transactionActions;

    /**
     * @var ExpressionFactory
     */
    protected $sql_expression;

    /**
     * Transaction constructor.
     * @param Context $context
     * @param Data $bssHelper
     * @param TransactionActions $transactionActions
     * @param \Magento\Framework\DB\Sql\ExpressionFactory $sql_expression
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        Data $bssHelper,
        TransactionActions $transactionActions,
        \Magento\Framework\DB\Sql\ExpressionFactory $sql_expression,
        $connectionName = null
    ) {
        $this->bssHelper = $bssHelper;
        $this->transactionActions = $transactionActions;
        $this->sql_expression = $sql_expression;
        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_reward_point_transaction', 'transaction_id');
    }

    /**
     * Get reward point info by customer
     *
     * @param int $customerId
     * @param int $websiteId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByCustomer($customerId, $websiteId)
    {
        $bind = ['customer_id' => $customerId, 'website_id' => $websiteId];
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            [
                'point_balance' => $this->getBsExpression('SUM(point - point_expired)'),
                'point_earned' => $this->getBsExpression('SUM(CASE WHEN point > 0 THEN point ELSE 0 END)'),
                'point_spent' => $this->getBsExpression('SUM(CASE WHEN point < 0 THEN point ELSE 0 END)'),
                'point_expired' => $this->getBsExpression('SUM(point_expired)'),
                'amount' => $this->getBsExpression('SUM(amount)')
            ]
        )->where(
            'customer_id = :customer_id'
        )->where(
            'website_id = :website_id'
        );

        return $this->getConnection()->fetchRow($select, $bind);
    }

    /**
     * Get Point balance
     *
     * @param array $bind
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPointBalanceReview($bind)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            [
                'point_balance' => $this->getBsExpression('SUM(point - point_expired)')
            ]
        )->where(
            'customer_id = :customer_id'
        )->where(
            'website_id = :website_id'
        )->where(
            'action = :action'
        )->where(
            'created_at > :from'
        )->where(
            'created_at < :to'
        );

        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * Get Point Balance to show in grid
     *
     * @param int $transactionId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPointBalanceForGrid($transactionId)
    {
        $select =  $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            ['customer_id', 'website_id']
        )->where('transaction_id = :transaction_id');
        $bind = [
            'transaction_id' => $transactionId
        ];
        $transactionInfo = $this->getConnection()->fetchRow($select, $bind);
        $select1 = $this->getConnection()->select()->from(
            $this->getMainTable(),
            [
                'point_balance' => $this->getBsExpression('SUM(point) - SUM(point_expired)')
            ]
        )->where(
            'customer_id = :customer_id'
        )->where(
            'website_id = :website_id'
        )->where('transaction_id <= :transaction_id');
        $bind1 = [
            'customer_id' => $transactionInfo['customer_id'],
            'website_id' => $transactionInfo['website_id'],
            'transaction_id' => $transactionId
        ];

        return $this->getConnection()->fetchOne($select1, $bind1);
    }

    /**
     * Get bs expression
     *
     * @param string $expression
     * @return \Magento\Framework\DB\Sql\Expression
     */
    protected function getBsExpression($expression)
    {
        return $this->sql_expression->create(['expression' => $expression]);
    }

    /**
     * Process import transaction data
     *
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processData($data)
    {
        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $data['website_id'] = $this->getWebsiteId($data['website_code']);
        $data['base_currrency_code'] = $this->bssHelper->getBaseCurrencyCode($data['website_id']);
        $data['basecurrency_to_point_rate'] = $this->getRate($data['customer_id'], $data['website_id']);
        $transactionOptions = $this->transactionActions->toArray();
        if (!isset($transactionOptions[$data['action']])) {
            $data['action'] = TransactionActions::IMPORT;
        }

        $expiredDay = $this->bssHelper->getValueConfig(
            'bssrewardpoint/general/expire_day',
            ScopeInterface::SCOPE_WEBSITE
        );

        /**
         * Check if maximum point per order
         */
        if ($data['action'] == TransactionActions::ORDER) {
            $maximumPerOrder = $this->bssHelper->getMaximumEarnPerOrder($data['website_id']);
            $data['point'] = ($maximumPerOrder > 0 && $data['point'] >= $maximumPerOrder)
                ? $maximumPerOrder : $data['point'];
        }

        /**
         * Check if Maximum balance point
         */
        $threshold = $this->bssHelper->getPointsMaximum();
        $balanceInfo = $this->loadByCustomer($data['customer_id'], $data['website_id']);
        $newBalance = $balanceInfo['point_balance'] + $data['point'];
        if ($threshold > 0) {
            $data['point'] = $newBalance >= $threshold ? $threshold - $balanceInfo['point_balance'] : $data['point'];
        }

        $data['expires_at'] = $expiredDay != null ? $expiredDay : 0;

        unset($data['website_code']);
        $this->getConnection()->insert($this->getMainTable(), $data);
        $this->insertedRows++;
    }

    /**
     * Get rate by customer
     *
     * @param int $customerId
     * @param int $websiteId
     * @return string
     */
    protected function getRate($customerId, $websiteId)
    {
        $select = $this->getConnection()->select()->from(
            ['customer' => $this->getTable('customer_entity')],
            []
        )->join(
            ['rate' => $this->getTable('bss_reward_point_rate')],
            'customer.group_id = rate.customer_group_id',
            ['rate.basecurrency_to_point_rate']
        )->where('customer.entity_id = ?', $customerId)->where('rate.website_id = ?', $websiteId);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Get website id by code
     *
     * @param string $websiteCode
     * @return string
     */
    protected function getWebsiteId($websiteCode)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('store_website'),
            ['website_id']
        )->where('code = ?', $websiteCode);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Validate import data
     *
     * @param array $data
     * @param int $rowNum
     */
    public function validateBeforeImport($data, $rowNum)
    {
        $invalid = false;
        if (empty($this->getWebsiteId($data['website_code']))) {
            $this->wrongWebsiteCode .= "$rowNum, ";
            $invalid = true;
        }

        $select = $this->getConnection()->select()->from(
            $this->getTable('customer_entity'),
            ['entity_id']
        )->where('entity_id = ?', $data['customer_id']);

        $customerId = $this->getConnection()->fetchOne($select);

        if (!$this->validateDate($data['created_at'], $rowNum)) {
            $invalid = true;
        }

        if (empty($customerId)) {
            $this->customerNotExist .= "$rowNum, ";
            $invalid = true;
        }

        if ($invalid) {
            $this->invalidDataRows++;
        }
    }

    /**
     * Validate date to import
     *
     * @param string $date
     * @param int $rowNum
     * @return bool
     */
    protected function validateDate($date, $rowNum)
    {
        $patternDate = '/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/';
        if (empty($date)) {
            return true;
        }

        if (!preg_match($patternDate, $date)) {
            $this->invalidDateRows .= "$rowNum, ";
            return false;
        }

        return true;
    }

    /**
     * Get count of row error
     *
     * @param string|null $code
     * @return int|string
     */
    public function getErrorRows($code = null)
    {
        switch ($code) {
            case "wrongWebsiteCode":
                return $this->wrongWebsiteCode;
            case "customerNotExist":
                return $this->customerNotExist;
            case "invalidDateRows":
                return $this->invalidDateRows;
            default:
                return $this->invalidDataRows;
        }
    }

    /**
     * Get invalid rows
     *
     * @return int
     */
    public function getInvalidRows()
    {
        return $this->invalidDataRows;
    }

    /**
     * Get inserted rows
     *
     * @return int
     */
    public function getInsertedRows()
    {
        return $this->insertedRows;
    }

    /**
     * Update point used of transaction
     *
     * @param \Bss\RewardPoint\Model\Transaction $transaction
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function updatePointUsed($transaction)
    {
        $connection = $this->getConnection();
        $createdAt = $transaction->getCreatedAt();
        $createdAt = strtotime($createdAt);
        $now = strtotime($this->bssHelper->getCreateAt());
        $daysLeft = ($now - $createdAt) / 86400;

        $websiteId = $transaction->getWebsiteId();
        $customerId = $transaction->getCustomerId();
        $point_use = ($transaction->getPoint() == null) ? 0 : abs($transaction->getPoint());
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'website_id  = :website_id'
        )->where(
            'customer_id = :customer_id'
        )->where(
            'expires_at  > :time_now OR expires_at IS NULL'
        )->where(
            'point - point_used > 0'
        )->order(
            ['is_expired desc','expires_at asc']
        );

        $bind = [':website_id' => $websiteId, ':customer_id' => $customerId, ':time_now' => $daysLeft];
        $pointsUsed = [];
        $stmt = $connection->query($select, $bind);
        while ($row = $stmt->fetch()) {
            if (!isset($pointsUsed[$row['transaction_id']])) {
                $point_row = $row['point'] - $row['point_used'];
                $pointsUsed[$row['transaction_id']] = (int)$point_row + $row['point_used'];
                if ($point_row >= $point_use) {
                    $pointsUsed[$row['transaction_id']] = $point_use + $row['point_used'];
                    break;
                }
                $point_use -= $point_row;
            }
        }

        if (!empty($pointsUsed)) {
            foreach ($pointsUsed as $transaction_id => $point_used) {
                $bind = ['point_used' => $point_used];
                $where = ['transaction_id=?' => $transaction_id];
                $connection->update($this->getMainTable(), $bind, $where);
            }
        }

        return $this;
    }

    /**
     * @param $orderId
     *
     * @return bool
     * @throws LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function checkOrderAddPoint($orderId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['transaction_id']
        )->where(
            'action = :action'
        )->where(
            'is_expired = :is_expired'
        )->where(
            'point > 0'
        )->join(
            ['order' => $this->getTable('sales_order')],
            'action_id = order.entity_id',
            ['']
        )->where(
            'order.entity_id = :entity_id'
        );
        $bind = [
            ':action' => TransactionActions::ORDER,
            ':is_expired' => 0,
            ':entity_id' => $orderId
        ];
        $stmt = $connection->query($select, $bind);
        while ($stmt->fetch()) {
            return true;
        }
        return false;
    }

    /**
     * Update all expired point
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updatePointExpired()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            [
                'transaction_id',
                'expires_at',
                'created_at',
                'calculate_point_expired' => $this->getBsExpression('point - point_used'),
            ]
        )->where(
            'point - point_used > ?',
            0
        )->where(
            'point_expired = ?',
            0
        );

        $points_expired = $connection->fetchAll($select);

        foreach ($points_expired as $key => $row) {
            $expiredDay = $row['expires_at'];
            $createdAt = $row['created_at'];
            $createdAt = strtotime($createdAt);
            $now = strtotime($this->bssHelper->getCreateAt());
            $daysLeft = ($now - $createdAt) / 86400;
            if ($daysLeft && $expiredDay && $expiredDay <= $daysLeft) {
                $bind = ['point_expired' => $row['calculate_point_expired']];
                $where = ['transaction_id=?' => $row['transaction_id']];
                $connection->update($this->getMainTable(), $bind, $where);
            }
            unset($points_expired[$key]);
        }
        return $this;
    }

    /**
     * Get last transaction id per customer
     *
     * @param int $customerId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLastTransactionIdsPerCustomer($customerId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            [
                'last_id' => 'MAX(transaction_id)'
            ]
        )->where('customer_id = ?', $customerId);
        return  $connection->fetchOne($select);
    }
}
