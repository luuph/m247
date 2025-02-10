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
namespace Bss\RewardPoint\Model\ResourceModel\Transaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'transaction_id';

    /**
     * @var \Magento\Framework\DB\Sql\ExpressionFactory
     */
    protected $sql_expression;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Sql\ExpressionFactory $sql_expression
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Sql\ExpressionFactory $sql_expression,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->sql_expression =  $sql_expression;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Initialization here
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Bss\RewardPoint\Model\Transaction::class,
            \Bss\RewardPoint\Model\ResourceModel\Transaction::class
        );
    }

    /**
     * Calculate balance
     *
     * @return $this
     */
    public function _calculateBalance()
    {
        $this->getSelect()->columns([
                'point_balance' => $this->getBsExpression('SUM(point - point_expired)'),
                'point_earned' => $this->getBsExpression('SUM(CASE WHEN point > 0 THEN point ELSE 0 END)'),
                'point_spent' => $this->getBsExpression('SUM(CASE WHEN point < 0 THEN point ELSE 0 END)'),

            ])->group('website_id');
        return $this;
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
     * Get csv header row
     *
     * @return array
     */
    protected function getCsvHeaderRow()
    {
        return [
            'website_code',
            'customer_id',
            'point',
            'action',
            'created_at',
            'note',
            'created_by',
            'is_expired'
        ];
    }

    /**
     * Get export data
     *
     * @return array
     */
    public function getExportData()
    {
        $headerRow = $this->getCsvHeaderRow();
        $data[] = $headerRow;
        foreach ($this->getData() as $transactionData) {
            $row = [];
            foreach ($headerRow as $key => $value) {
                if ($value == 'website_code') {
                    $row[$key] = $this->getWebsiteCode($transactionData['website_id']);
                } else {
                    $row[$key] = $transactionData[$value];
                }
            }
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Get website code
     *
     * @param string $websiteId
     * @return string
     */
    protected function getWebsiteCode($websiteId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('store_website'),
            ['code']
        )->where('website_id = ?', $websiteId);

        return $this->getConnection()->fetchOne($select);
    }
}
