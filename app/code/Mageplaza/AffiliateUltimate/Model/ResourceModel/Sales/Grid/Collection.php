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

namespace Mageplaza\AffiliateUltimate\Model\ResourceModel\Sales\Grid;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AffiliateUltimate\Helper\Data;
use Mageplaza\AffiliateUltimate\Model\ResourceModel\ProcessData;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * @package Mageplaza\AffiliateUltimate\Model\ResourceModel\Sales\Grid
 */
class Collection extends ProcessData
{
    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param RequestInterface $request
     * @param Data $helperData
     * @param string $mainTable
     * @param string $resourceModel
     *
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        RequestInterface $request,
        Data $helperData,
        $mainTable = 'sales_order_item',
        $resourceModel = 'Magento\Sales\Model\ResourceModel\Order\Item'
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $request,
            $helperData,
            $mainTable,
            $resourceModel
        );
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()])->joinLeft(
            ['sales_order' => $this->getTable('sales_order')],
            'sales_order.entity_id = main_table.order_id',
            ['order_status' => 'status']
        );

        $this->addFieldToFilter('main_table.affiliate_commission', ['neq' => 'NULL']);
        $this->addDateToFilter()
            ->addStoreToFilter()
            ->addOrderStatusToFilter();

        return $this;
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return $this|void
     */
    public function addFieldToFilter($field, $condition = null)
    {
        $customFilters = ['name', 'email', 'total_sales_amount', 'commission', 'period'];
        if (!in_array($field, $customFilters)) {
            parent::addFieldToFilter($field, $condition);
        }
    }
}
