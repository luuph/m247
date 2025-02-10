<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Model\Report\ResourceModel;

use Amasty\Rma\Api\Data\ConditionInterface;
use Amasty\Rma\Api\Data\ReasonInterface;
use Amasty\Rma\Api\Data\RequestInterface;
use Amasty\Rma\Api\Data\RequestItemInterface;
use Amasty\Rma\Model\Condition\ResourceModel\Condition;
use Amasty\Rma\Model\Reason\ResourceModel\Reason;
use Amasty\Rma\Model\Request\ResourceModel\Request;
use Amasty\Rma\Model\Request\ResourceModel\RequestItem;
use Amasty\Rma\Model\Resolution\ResourceModel\Resolution;
use Amasty\RmaReports\Model\DateProcessor;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\DB\Select;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Amasty\Rma\Api\Data\ResolutionInterface;

/**
 * Class ItemCollection
 */
class ReportCollectionsGenerator
{
    public const LIMIT = 100;

    /**
     * @var \Amasty\Rma\Model\Request\ResourceModel\RequestItemCollectionFactory
     */
    private $requestItemCollectionFactory;

    /**
     * @var CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var \Amasty\Rma\Model\Request\ResourceModel\CollectionFactory
     */
    private $requestCollectionFactory;

    /**
     * @var StatsFactory
     */
    private $statsFactory;

    /**
     * @var DateProcessor
     */
    private $dateProcessor;

    /**
     * @var string
     */
    private $currencySymbol = "$";

    public function __construct(
        \Amasty\Rma\Model\Request\ResourceModel\RequestItemCollectionFactory $requestItemCollectionFactory,
        \Amasty\Rma\Model\Request\ResourceModel\CollectionFactory $requestCollectionFactory,
        CollectionFactory $customerCollectionFactory,
        StatsFactory $statsFactory,
        DateProcessor $dateProcessor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->requestItemCollectionFactory = $requestItemCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->requestCollectionFactory = $requestCollectionFactory;
        $this->statsFactory = $statsFactory;
        $this->dateProcessor = $dateProcessor;
        $this->currencySymbol = $currencyFactory->create()->load($storeManager->getStore()->getCurrentCurrencyCode())
            ->getCurrencySymbol();
    }

    /**
     * @param int $reasonId
     *
     * @return \Amasty\Rma\Model\Request\ResourceModel\RequestItemCollection
     */
    public function getReasonItemsCollection($reasonId)
    {
        [$dateFrom, $dateTo] = $this->dateProcessor->getFromToDate();

        /** @var Stats $stats */
        $stats = $this->statsFactory->create([
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ]);
        $requestItemsCollection = $stats->getResolvedItems();
        $this->addOrderItems($requestItemsCollection);
        $requestItemsCollection->addFieldToFilter('main_table.' . RequestItemInterface::REASON_ID, (int)$reasonId);
        $requestItemsCollection->getSelect()
            ->group('so.' . OrderItemInterface::PRODUCT_ID)
            ->limit(self::LIMIT);
        $stats->applyDateFilter($requestItemsCollection->getSelect(), RequestInterface::MODIFIED_AT);

        return $requestItemsCollection;
    }

    /**
     * @param \Amasty\Rma\Model\Request\ResourceModel\RequestItemCollection $requestItemsCollection
     */
    private function addOrderItems($requestItemsCollection)
    {
        $requestItemsCollection->join(
            ['so' => $requestItemsCollection->getTable('sales_order_item')],
            'main_table.' . RequestItemInterface::ORDER_ITEM_ID . ' = so.' . OrderItemInterface::ITEM_ID,
            [
                'price' => new \Zend_Db_Expr('concat("' . $this->currencySymbol
                    . '", cast(so.' . OrderItemInterface::BASE_PRICE . ' as decimal(10,2)))'),
                'so.' . OrderItemInterface::NAME,
                'so.' . OrderItemInterface::PRODUCT_ID,
                'times' => new \Zend_Db_Expr('cast(sum(main_table.'
                    . RequestItemInterface::REQUEST_QTY . ') as decimal)')
            ]
        );
    }

    /**
     * @param int $resolutionId
     *
     * @return \Amasty\Rma\Model\Request\ResourceModel\Collection
     */
    public function getCustomerItemsCollection($resolutionId)
    {
        $customerCollection = $this->requestCollectionFactory->create();
        $select = $customerCollection->getSelect();
        $select->reset()->from(
            ['customer' => $customerCollection->getTable('customer_entity')],
            [
                'customer_name' => new \Zend_Db_Expr("CONCAT(customer.firstname, ' ', customer.lastname)"),
                'customer_id'   => 'customer.entity_id',
                'order_qty'     => new \Zend_Db_Expr(
                    "CONCAT(count(salesorder."
                    . OrderInterface::ENTITY_ID . "), ' (" . $this->currencySymbol . "', cast(sum(salesorder."
                    . OrderInterface::BASE_GRAND_TOTAL . ") as decimal(10,2)), ')')"
                ),
                'rma_qty'       => new \Zend_Db_Expr(
                    "CONCAT((" . $this->getRmaCountSelect()
                    . "), ' (" . $this->currencySymbol . "', cast(("
                    . $this->getRmaSumSelectByResolutionId($resolutionId)
                    . ") as decimal(10,2)), ')')"
                ),
                'profit' => new \Zend_Db_Expr(
                    "CONCAT('" . $this->currencySymbol . "', cast(SUM(salesorder."
                    . OrderInterface::BASE_GRAND_TOTAL . ") - ("
                    . $this->getRmaSumSelectByResolutionId($resolutionId) . ') as decimal(10,2)))'
                )
            ]
        )->joinInner(
            ['salesorder' => $customerCollection->getTable('sales_order')],
            'salesorder.' . OrderInterface::CUSTOMER_ID . ' = customer.entity_id',
            []
        )->where(
            'customer.entity_id IN (' . $this->getCustomersSelectByResolutionId($resolutionId) . ')'
        )->group('customer.entity_id')->limit(self::LIMIT);

        return $customerCollection;
    }

    /**
     * @param int $resolutionId
     *
     * @return \Magento\Framework\DB\Select
     */
    private function getCustomersSelectByResolutionId($resolutionId)
    {
        [$dateFrom, $dateTo] = $this->dateProcessor->getFromToDate();

        /** @var Stats $stats */
        $stats = $this->statsFactory->create([
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ]);
        $requestItemsCollection = $stats->getResolvedItems();
        $requestItemsCollection->getSelect()->reset(Select::COLUMNS)->columns(
            ['rmar.' . RequestInterface::CUSTOMER_ID]
        )->joinInner(
            ['rmar' => $requestItemsCollection->getTable(Request::TABLE_NAME)],
            'main_table.' . RequestItemInterface::REQUEST_ID . ' = rmar.' . RequestInterface::REQUEST_ID,
            []
        )->where(RequestItemInterface::RESOLUTION_ID . ' = ?', (int)$resolutionId)
            ->group('rmar.' . RequestInterface::CUSTOMER_ID);
        $stats->applyDateFilter($requestItemsCollection->getSelect(), 'rmar.' . RequestInterface::MODIFIED_AT);

        return $requestItemsCollection->getSelect();
    }

    /**
     * @param int $resolutionId
     *
     * @return \Magento\Framework\DB\Select
     */
    private function getRmaSumSelectByResolutionId($resolutionId)
    {
        $requestItemsCollection = $this->requestItemCollectionFactory->create();
        $requestItemsCollection->getSelect()->reset()->from(
            ['ri' => $requestItemsCollection->getTable(RequestItem::TABLE_NAME)],
            [
                new \Zend_Db_Expr(
                    'SUM(soi.' . OrderItemInterface::BASE_PRICE
                    . ' * ri.' . RequestItemInterface::REQUEST_QTY . ')'
                )
            ]
        )->joinLeft(
            ['soi' => $requestItemsCollection->getTable('sales_order_item')],
            'ri.' . RequestItemInterface::ORDER_ITEM_ID . ' = soi.' . OrderItemInterface::ITEM_ID,
            []
        )->joinInner(
            ['rma2' => $requestItemsCollection->getTable(Request::TABLE_NAME)],
            'rma2.' . RequestInterface::REQUEST_ID . ' = ri.' . RequestItemInterface::REQUEST_ID,
            []
        );
        $requestItemsCollection->getSelect()->where(
            "(rma2." . RequestInterface::CUSTOMER_ID
            . " = customer.entity_id) AND (ri." . RequestItemInterface::RESOLUTION_ID
            . " = " . (int)$resolutionId . ")"
        );

        return $requestItemsCollection->getSelect();
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    private function getRmaCountSelect()
    {
        $requestCollection = $this->requestCollectionFactory->create();
        $requestCollection->getSelect()->reset()->from(
            ['rma' => $requestCollection->getTable(Request::TABLE_NAME)],
            [new \Zend_Db_Expr('count(rma.' . RequestInterface::REQUEST_ID . ')')]
        )->where('rma.' . RequestInterface::CUSTOMER_ID . ' = customer.entity_id');

        return $requestCollection->getSelect();
    }

    /**
     * @return \Amasty\Rma\Model\Request\ResourceModel\Collection
     */
    public function getDetailsCollection()
    {
        [$dateFrom, $dateTo] = $this->dateProcessor->getFromToDate();

        /** @var Stats $stats */
        $stats = $this->statsFactory->create([
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ]);
        $requestCollection = $stats->getResolvedRequests();
        $requestCollection->getSelect()->reset(Select::COLUMNS)
            ->columns(
                [
                    'main_table.' . RequestInterface::REQUEST_ID,
                    'main_table.' . RequestInterface::CUSTOMER_NAME,
                    'main_table.' . RequestInterface::MANAGER_ID,
                    'lead_time' => new \Zend_Db_Expr(
                        'CONCAT(DATEDIFF(main_table.' . RequestInterface::MODIFIED_AT
                        . ', main_table.' . RequestInterface::CREATED_AT . '), " Days")'
                    ),
                    'main_table.' . RequestInterface::RATING
                ]
            )->group('main_table.' . RequestInterface::REQUEST_ID);
        $this->addTotalsToCollection($requestCollection);
        $this->addConcatedFieldsToCollection($requestCollection);
        $stats->applyDateFilter($requestCollection->getSelect(), RequestInterface::MODIFIED_AT);

        return $requestCollection;
    }

    /**
     * @param \Amasty\Rma\Model\Request\ResourceModel\Collection $collection
     */
    private function addTotalsToCollection($collection)
    {
        $collection->getSelect()->joinLeft(
            ['rmai' => $collection->getTable(RequestItem::TABLE_NAME)],
            'main_table.' . RequestInterface::REQUEST_ID . ' = rmai.' . RequestItemInterface::REQUEST_ID,
            []
        )->joinLeft(
            ['soi' => $collection->getTable('sales_order_item')],
            'rmai.' . RequestItemInterface::ORDER_ITEM_ID . ' = soi.item_id',
            [
                'total' => new \Zend_Db_Expr(
                    'CAST(SUM(soi.price * rmai.'
                    . RequestItemInterface::REQUEST_QTY . ') AS DECIMAL(10,2))'
                ),
                'skus' => new \Zend_Db_Expr(
                    'GROUP_CONCAT(soi.sku SEPARATOR "<br/>")'
                )
            ]
        );
    }

    /**
     * @param \Amasty\Rma\Model\Request\ResourceModel\Collection $collection
     */
    private function addConcatedFieldsToCollection($collection)
    {
        $collection->getSelect()->joinLeft(
            ['res' => $collection->getTable(Resolution::TABLE_NAME)],
            'rmai.' . RequestItemInterface::RESOLUTION_ID . ' = res.' . ResolutionInterface::RESOLUTION_ID,
            [
                'resolutions' => new \Zend_Db_Expr(
                    'GROUP_CONCAT(res.' . ResolutionInterface::TITLE . ' SEPARATOR "<br/>")'
                )
            ]
        )->joinLeft(
            ['rea' => $collection->getTable(Reason::TABLE_NAME)],
            'rmai.' . RequestItemInterface::REASON_ID . ' = rea.' . ReasonInterface::REASON_ID,
            [
                'reasons' => new \Zend_Db_Expr(
                    'GROUP_CONCAT(rea.' . ReasonInterface::TITLE . ' SEPARATOR "<br/>")'
                )
            ]
        )->joinLeft(
            ['con' => $collection->getTable(Condition::TABLE_NAME)],
            'rmai.' . RequestItemInterface::CONDITION_ID . ' = con.' . ConditionInterface::CONDITION_ID,
            [
                'conditions' => new \Zend_Db_Expr(
                    'GROUP_CONCAT(con.' . ConditionInterface::TITLE . ' SEPARATOR "<br/>")'
                )
            ]
        );
    }
}
