<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Model\Report\ResourceModel;

use Amasty\Rma\Api\Data\ReasonInterface;
use Amasty\Rma\Api\Data\RequestInterface;
use Amasty\Rma\Api\Data\RequestItemInterface;
use Amasty\Rma\Api\Data\StatusInterface;
use Amasty\Rma\Model\OptionSource\ItemStatus;
use Amasty\Rma\Model\OptionSource\ShippingPayer;
use Amasty\Rma\Model\OptionSource\State;
use Amasty\Rma\Model\Reason\ResourceModel\Reason;
use Amasty\Rma\Model\Request\ResourceModel\Collection as RequestCollection;
use Amasty\Rma\Model\Request\ResourceModel\CollectionFactory as RequestCollectionFactory;
use Amasty\Rma\Model\Request\ResourceModel\Request as RequestResource;
use Amasty\Rma\Model\Request\ResourceModel\RequestItem as RequestItem;
use Amasty\Rma\Model\Request\ResourceModel\RequestItemCollection;
use Amasty\Rma\Model\Request\ResourceModel\RequestItemCollectionFactory;
use Amasty\Rma\Model\Status\ResourceModel\Status;
use Magento\Framework\DB\Select;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Stats
{
    public const TOP_REASONS_LIMIT = 5;

    /**
     * @var RequestItemCollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var RequestCollectionFactory
     */
    private $requestCollectionFactory;

    /**
     * @var CollectionFactory
     */
    private $ordersCollectionFactory;

    /**
     * @var \DateTime|null
     */
    private $dateFrom;

    /**
     * @var \DateTime|null
     */
    private $dateTo;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var array
     */
    private $items;

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    public function __construct(
        RequestItemCollectionFactory $itemCollectionFactory,
        RequestCollectionFactory $requestCollectionFactory,
        CollectionFactory $ordersCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        $dateFrom = null,
        $dateTo = null,
        $namespace = '',
        $items = []
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->requestCollectionFactory = $requestCollectionFactory;
        $this->ordersCollectionFactory = $ordersCollectionFactory;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->namespace = $namespace;
        $this->items = $items;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @return array
     */
    public function getTopReasons()
    {
        $requests = $this->getResolvedRequests();
        $requests->join(
            ['item' => $requests->getTable(RequestItem::TABLE_NAME)],
            'item.' . RequestItemInterface::REQUEST_ID . ' = main_table.' . RequestInterface::REQUEST_ID,
            []
        )->join(
            ['reason' => $requests->getTable(Reason::TABLE_NAME)],
            'reason.' . ReasonInterface::REASON_ID . ' = item.' . RequestItemInterface::REASON_ID,
            []
        );

        $requests->getSelect()->reset(Select::COLUMNS)
            ->columns(
                [
                    'title' => 'reason.' . ReasonInterface::TITLE,
                    'qty' => new \Zend_Db_Expr('SUM(item.' . RequestItemInterface::QTY . ')')
                ]
            )->order(new \Zend_Db_Expr('SUM(item.' . RequestItemInterface::QTY . ') DESC'))
            ->limit(self::TOP_REASONS_LIMIT)
            ->group('reason.' . ReasonInterface::REASON_ID);

        $this->applyDateFilter($requests->getSelect(), RequestInterface::MODIFIED_AT);

        return array_values($requests->getConnection()->fetchAssoc($requests->getSelect()));
    }

    /**
     * @return array
     */
    public function getTotalRequests()
    {
        $requests = $this->getResolvedRequests();
        $requests->getSelect()->reset(Select::COLUMNS)
            ->where(RequestInterface::MODIFIED_AT)
            ->columns(
                [
                    'date' => new \Zend_Db_Expr('date(' . RequestInterface::MODIFIED_AT . ')'),
                    'count' => new \Zend_Db_Expr('COUNT(*)')
                ]
            )->group(new \Zend_Db_Expr('date(' . RequestInterface::MODIFIED_AT . ')'))
            ->order(new \Zend_Db_Expr('date(' . RequestInterface::MODIFIED_AT . ')'));

        $this->applyDateFilter($requests->getSelect(), RequestInterface::MODIFIED_AT);
        $this->applyItemsFilter($requests->getSelect());
        $totalsData = array_values($requests->getConnection()->fetchAssoc($requests->getSelect()));
        $requestsCount = 0;

        foreach ($totalsData as $item) {
            $requestsCount += $item['count'];
        }

        return [$totalsData, $requestsCount];
    }

    /**
     * @return array
     */
    public function getReturnPercentage()
    {
        $orders = $this->ordersCollectionFactory->create();
        $validRequestIdsSelect = $this->getResolvedRequests()->getSelect()->reset(Select::COLUMNS)
            ->columns([RequestInterface::REQUEST_ID, RequestInterface::ORDER_ID, RequestInterface::MODIFIED_AT]);
        $this->applyDateFilter(
            $validRequestIdsSelect,
            new \Zend_Db_Expr('date(main_table.' . RequestInterface::MODIFIED_AT . ')')
        );
        $this->applyItemsFilter($validRequestIdsSelect);

        $orders->getSelect()->reset(Select::COLUMNS)->columns(
            [
                'date' => new \Zend_Db_Expr(
                    'ifnull(date(main_table.updated_at), date(rma.'
                    . RequestInterface::MODIFIED_AT . '))'
                ),
                'o_count' => new \Zend_Db_Expr('count(main_table.entity_id)'),
                'r_count' => new \Zend_Db_Expr('count(rma.' . RequestInterface::REQUEST_ID . ')')
            ]
        )->joinLeft(
            ['rma' => $validRequestIdsSelect],
            'main_table.entity_id = rma.' . RequestInterface::ORDER_ID,
            []
        )->group(
            'date'
        );
        $totalPercentage = [];
        $ordersCount = 0;
        $returnsCount = 0;

        foreach ($orders->getData() as $record) {
            $ordersCount += $record['o_count'];
            $returnsCount += $record['r_count'];
            $totalPercentage[] = [
                'date' => $record['date'],
                'count' => number_format($returnsCount * 100 / $ordersCount, 2) . '%'
            ];
        }

        return [$totalPercentage, $ordersCount ? number_format($returnsCount * 100 / $ordersCount, 2) . '%' : null];
    }

    /**
     * @return array
     */
    public function getLeadTime()
    {
        $requests = $this->getResolvedRequests();
        $requests->getSelect()->reset(Select::COLUMNS)->columns(
            [
                'date' => new \Zend_Db_Expr('date(main_table.' . RequestInterface::MODIFIED_AT . ')'),
                'days' => $this->getResolvedRequests()->getSelect()->reset(Select::FROM)
                    ->reset(Select::COLUMNS)
                    ->from(
                        ['rma2' => $requests->getTable(RequestResource::TABLE_NAME)],
                        [
                            'days' => new \Zend_Db_Expr(
                                'sum(datediff(rma2.' . RequestInterface::MODIFIED_AT . ', rma2.'
                                . RequestInterface::CREATED_AT . '))'
                            )
                        ]
                    )->where(
                        new \Zend_Db_Expr(
                            'date(rma2.' . RequestInterface::MODIFIED_AT . ') = date(main_table.'
                            . RequestInterface::MODIFIED_AT . ')'
                        )
                    ),
                'r_count' => new \Zend_Db_Expr('count(main_table.' . RequestInterface::REQUEST_ID . ')')
            ]
        )->group(new \Zend_Db_Expr('date(main_table.' . RequestInterface::MODIFIED_AT . ')'))
            ->order(new \Zend_Db_Expr('date(main_table.' . RequestInterface::MODIFIED_AT . ')'));
        $this->applyDateFilter($requests->getSelect(), RequestInterface::MODIFIED_AT);
        $this->applyItemsFilter($requests->getSelect());
        $leadTime = 0;
        $count = 0;
        $leadTimeTotal = [];

        foreach ($requests->getData() as $record) {
            $leadTime += $record['days'];
            $count += $record['r_count'];
            $leadTimeTotal[] = [
                'date'  => $record['date'],
                'count' => number_format($leadTime / $count, 2)
            ];
        }

        return [$leadTimeTotal, $count ? number_format($leadTime / $count, 2) : null];
    }

    /**
     * @return array
     */
    public function getRating()
    {
        $requests = $this->getResolvedRequests();
        $requests->getSelect()->reset(Select::COLUMNS)
            ->columns(
                [
                    'date' => new \Zend_Db_Expr('date(main_table.' . RequestInterface::MODIFIED_AT . ')'),
                    'rating' => $this->getResolvedRequests()->getSelect()->reset(Select::FROM)
                        ->reset(Select::COLUMNS)
                        ->from(
                            ['rma2' => $requests->getTable(RequestResource::TABLE_NAME)],
                            new \Zend_Db_Expr('sum(rma2.' . RequestInterface::RATING . ')')
                        )->where(
                            new \Zend_Db_Expr(
                                'date(rma2.' . RequestInterface::MODIFIED_AT . ') = date(main_table.'
                                . RequestInterface::MODIFIED_AT . ')'
                            )
                        ),
                    'r_count' => new \Zend_Db_Expr('count(main_table.' . RequestInterface::REQUEST_ID . ')')
                ]
            )->where('main_table.' . RequestInterface::RATING . ' > 0')->group(
                new \Zend_Db_Expr('date(main_table.' . RequestInterface::MODIFIED_AT . ')')
            );
        $this->applyDateFilter($requests->getSelect(), RequestInterface::MODIFIED_AT);
        $this->applyItemsFilter($requests->getSelect());
        $rating = 0;
        $count = 0;
        $ratingTotal = [];

        foreach ($requests->getData() as $record) {
            $rating += $record['rating'];
            $count += $record['r_count'];
            $ratingTotal[] = [
                'date' => $record['date'],
                'count' => number_format($rating / $count, 1)
            ];
        }

        return [$ratingTotal, $count ? number_format($rating / $count, 1) : null];
    }

    /**
     * @return array
     */
    public function getStoreDeliver()
    {
        $requestItems = $this->getResolvedItems();
        $requestItems->getSelect()->reset(Select::COLUMNS)->columns(
            [
                'date' => new \Zend_Db_Expr('date(request.' . RequestInterface::MODIFIED_AT . ')'),
                'deliver' => new \Zend_Db_Expr(
                    'count(main_table.' . RequestItemInterface::REQUEST_ID . ')'
                )
            ]
        )->joinInner(
            ['reason' => $requestItems->getTable(Reason::TABLE_NAME)],
            'main_table.' . RequestItemInterface::REASON_ID . '= reason.' . ReasonInterface::REASON_ID,
            []
        )->where(
            'reason.' . ReasonInterface::PAYER . ' = ' . ShippingPayer::STORE_OWNER
        )->group(
            new \Zend_Db_Expr('date(request.' . RequestInterface::MODIFIED_AT . ')')
        );
        $this->applyDateFilter($requestItems->getSelect(), RequestInterface::MODIFIED_AT);
        $count = 0;
        $totalStoreDeliver = [];

        foreach ($requestItems->getData() as $record) {
            $count += $record['deliver'];
            $totalStoreDeliver[] = [
                'date' => $record['date'],
                'count' => $count
            ];
        }

        return [$totalStoreDeliver, $count];
    }

    /**
     * @return RequestCollection
     */
    public function getResolvedRequests()
    {
        $requests = $this->requestCollectionFactory->create();
        $requests->join(
            ['status' => $requests->getTable(Status::TABLE_NAME)],
            'main_table.' . RequestInterface::STATUS . ' = status.' . StatusInterface::STATUS_ID,
            ['state' => 'status.' . StatusInterface::STATE]
        );
        $requests->addFieldToFilter('status.' . StatusInterface::STATE, State::RESOLVED);

        return $requests;
    }

    /**
     * @return RequestItemCollection
     */
    public function getResolvedItems()
    {
        $items = $this->itemCollectionFactory->create();
        $items->join(
            ['request' => $items->getTable(RequestResource::TABLE_NAME)],
            'main_table.' . RequestItemInterface::REQUEST_ID . ' = request.' . RequestInterface::REQUEST_ID,
            []
        )->join(
            ['status' => $items->getTable(Status::TABLE_NAME)],
            'request.' . RequestInterface::STATUS . ' = status.' . StatusInterface::STATUS_ID,
            ['state' => 'status.' . StatusInterface::STATE]
        );
        $items->addFieldToFilter('state', State::RESOLVED);
        $items->addFieldToFilter(RequestItemInterface::ITEM_STATUS, ['neq' => ItemStatus::REJECTED]);

        return $items;
    }

    /**
     * @param \Zend_Db_Select $select
     * @param string $column
     */
    public function applyDateFilter($select, $column)
    {
        if ($this->dateFrom) {
            $select->where("date($column) >= ?", $this->dateFrom->format('Y-m-d'));
        }

        if ($this->dateTo) {
            $select->where("date($column) <= ?", $this->dateTo->format('Y-m-d'));
        }
    }

    /**
     * @param \Zend_Db_Select $select
     * @param string $column
     */
    public function applyItemsFilter($select, $column = 'main_table.' . RequestInterface::REQUEST_ID)
    {
        if ($this->namespace === 'amrmarep_report_details_form') {
            $requestIds = [];

            foreach ($this->items as $item) {
                $requestIds[] = (int)$item['request_id'];
            }
            $select->where($column . ' IN (?)', $requestIds);
        }
    }
}
