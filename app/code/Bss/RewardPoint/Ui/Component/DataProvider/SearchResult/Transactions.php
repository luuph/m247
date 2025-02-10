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

namespace Bss\RewardPoint\Ui\Component\DataProvider\SearchResult;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Transactions extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{

    /**
     * @var \Magento\Framework\DB\Sql\ExpressionFactory
     */
    private $sql_expression;

    /**
     * Transactions constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param \Magento\Framework\DB\Sql\ExpressionFactory $sql_expression
     * @param string $mainTable
     * @param null|string $resourceModel
     * @param string $identifierName
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EntityFactory                               $entityFactory,
        Logger                                      $logger,
        FetchStrategy                               $fetchStrategy,
        EventManager                                $eventManager,
        \Magento\Framework\DB\Sql\ExpressionFactory $sql_expression,
        $mainTable = 'bss_reward_point_transaction',
        $resourceModel = null,
        $identifierName = 'transaction_id'
    ) {
        $this->sql_expression = $sql_expression;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel,
            $identifierName
        );
    }

    /**
     * Init select
     *
     * @return $this|void
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(
            ['main_table' => $this->getMainTable()],
            [
                'transaction_id',
                'point',
                'note',
                'created_at',
                'action',
                'created_by',
                'website_id',
                'point_used',
                'point_expired',
                'expires_at',
                'point_balance' => 'point'
            ]
        )->join(
            ['customer' => $this->getTable('customer_entity')],
            'main_table.customer_id = customer.entity_id',
            [
                'email' => 'customer.email',
                'customer_name' => 'CONCAT_WS(" ", customer.firstname, customer.lastname)'
            ]
        );
        $this->addFilterToMap('website_id', 'main_table.website_id');
        $this->addFilterToMap(
            'customer_name',
            $this->getBsExpression('CONCAT_WS(" ", customer.firstname, customer.lastname)')
        );
        $this->addFilterToMap('point_balance', $this->getBsExpression('(SUM(point) - SUM(point_expired))'));
        return $this;
    }

    /**
     * Add field to filter
     *
     * @param array|string $field
     * @param array $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (is_array($field)) {
            $conditions = [];
            foreach ($field as $key => $value) {
                $conditions[] = $this->_translateCondition($value, isset($condition[$key]) ? $condition[$key] : null);
            }

            $resultCondition = '(' . implode(') ' . \Magento\Framework\DB\Select::SQL_OR . ' (', $conditions) . ')';
        } else {
            $resultCondition = $this->_translateCondition($field, $condition);
        }

        if ($field == 'point_balance') {
            $this->_select->group('transaction_id')->having($resultCondition, null, Select::TYPE_CONDITION);
        } else {
            $this->_select->where($resultCondition, null, Select::TYPE_CONDITION);
        }

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
}
