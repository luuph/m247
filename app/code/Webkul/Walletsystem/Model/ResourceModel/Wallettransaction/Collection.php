<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Model\ResourceModel\Wallettransaction;

use \Webkul\Walletsystem\Model\ResourceModel\AbstractCollection;

/**
 * Webkul Walletsystem Class
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\Walletsystem\Model\Wallettransaction::class,
            \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction::class
        );
        $this->addFilterToMap(
            'entity_id',
            'main_table.entity_id'
        )->addFilterToMap(
            'customer_id',
            'main_table.customer_id'
        )->addFilterToMap(
            'status',
            'main_table.status'
        );
    }

    /**
     * Add store filter
     *
     * @param object $store
     * @param boolean $withAdmin
     * @return this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
    
    /**
     * Get Monthly Transaction Details function
     *
     * @param int $customerId
     * @param date $firstDay
     * @param date $lastDay
     * @return array
     */
    public function getMonthlyTransactionDetails($customerId, $firstDay, $lastDay)
    {
        $transactionColl = $this->addFieldToFilter('customer_id', $customerId)
                                    ->addFieldToFilter('status', 1)
                                    ->addFieldToFilter('main_table.transaction_at', ['gteq' => $firstDay])
                                    ->addFieldToFilter('main_table.transaction_at', ['lteq' => $lastDay]);
        $admincredit = $this->addAlias(clone $transactionColl, 3, 'credit');
        $admindebit = $this->addAlias(clone $transactionColl, 3, 'debit');
        $transfertobank = $this->addAlias(clone $transactionColl, 5, 'debit');
        $cashbackamount = $this->addAlias(clone $transactionColl, 1, 'credit');
        $customercredits = $this->addAlias(clone $transactionColl, 4, 'credit');
        $transfertocustomer = $this->addAlias(clone $transactionColl, 4, 'debit');
        $usedwallet = $this->addAlias(clone $transactionColl, 0, 'credit');
        $rechargewallet = $this->addAlias(clone $transactionColl, 0, 'debit');
        $refundamount = $this->addAlias(clone $transactionColl, 2, 'credit');
        $refundwalletorder = $this->addAlias(clone $transactionColl, 2, 'debit');
        $transactionColl->getSelect()->reset('columns')
                                        ->columns('customer_id')
                                        ->columns('transaction_at')
                                        ->columns(['admincredit' => $admincredit])
                                        ->columns(['admindebit' => $admindebit])
                                        ->columns(['transfertobank' => $transfertobank])
                                        ->columns(['cashbackamount' => $cashbackamount])
                                        ->columns(['customercredits' => $customercredits])
                                        ->columns(['transfertocustomer' => $transfertocustomer])
                                        ->columns(['usedwallet' => $usedwallet])
                                        ->columns(['rechargewallet' => $rechargewallet])
                                        ->columns(['refundamount' => $refundamount])
                                        ->columns(['refundwalletorder' => $refundwalletorder]);

        $transactionColl->getSelect()->group('main_table.customer_id');
        $transactionCollection = clone $transactionColl;

        $transactionCollection->getSelect()
                                ->from(['wallet1' => $transactionColl->getSelect()])
                                ->reset('columns')
                                ->columns('wallet1.admincredit')
                                ->columns('wallet1.cashbackamount')
                                ->columns('wallet1.customercredits')
                                ->columns('wallet1.rechargewallet')
                                ->columns('wallet1.refundamount')
                                ->columns('wallet1.refundwalletorder')
                                ->columns('wallet1.usedwallet')
                                ->columns('wallet1.admindebit')
                                ->columns('wallet1.transfertocustomer')
                                ->columns('wallet1.transfertobank')
                                ->columns(
                                    ['totaldebit' => "(
                                        `wallet1`.`refundwalletorder`+
                                        `wallet1`.`usedwallet`+
                                        `wallet1`.`admindebit`+
                                        `wallet1`.`transfertocustomer`+
                                        `wallet1`.`transfertobank`
                                    )"]
                                )
                                ->columns(
                                    ['totalcredit' => "(
                                        `wallet1`.`admincredit`+
                                        `wallet1`.`cashbackamount`+
                                        `wallet1`.`customercredits`+
                                        `wallet1`.`rechargewallet`+
                                        `wallet1`.`refundamount`
                                    )"]
                                );

        return  $transactionCollection->getData();
    }
    
    /**
     * Add Alias
     *
     * @param collection $collection
     * @param string $senderType
     * @param string $action
     * @return array
     */
    public function addAlias($collection, $senderType, $action)
    {
        $collection = $collection->addFieldToFilter('sender_type', $senderType)
                                    ->addFieldToFilter('action', $action);
        return $collection->getSelect()->reset('columns')->columns("COALESCE(sum(amount), 0)");
    }

    /**
     * Set table records
     *
     * @param array $ids
     * @param array $columnData
     * @return array
     */
    public function setTableRecords($ids, $columnData)
    {
        return $this->getConnection()->update(
            $this->getTable('wk_ws_wallet_transaction'),
            $columnData,
            $where = $ids
        );
    }

    /**
     * Retrieve all entity_ids for collection
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('entity_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * Retrieve clear select
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function _getClearSelect()
    {
        return $this->_buildClearSelect();
    }

    /**
     * Build clear select
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    protected function _buildClearSelect($select = null)
    {
        if (null === $select) {
            $select = clone $this->getSelect();
        }
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);

        return $select;
    }
}
