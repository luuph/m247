<?php

namespace Meetanshi\ImageClean\Model\ResourceModel\Imageclean\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Catalog\Model\ProductFactory;
use Meetanshi\ImageClean\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Collection extends \Meetanshi\ImageClean\Model\ResourceModel\Imageclean\Collection implements SearchResultInterface
{
    protected $aggregations;
    protected $scopeConfig;
    protected $curstoreid;
    private $productFactory;
    private $collectionFactory;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        ProductFactory $productFactory,
        Data $data,
        CollectionFactory $collectionFactory,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
    ) {

        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
        $this->productFactory = $productFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $productFactory, $data, $collectionFactory);
    }

    public function getAggregations()
    {
        return $this->aggregations;
    }

    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    public function getSearchCriteria()
    {
        return null;
    }

    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    public function getTotalCount()
    {
        return $this->getSize();
    }

    public function setTotalCount($totalCount)
    {
        return $this;
    }

    public function setItems(array $items = null)
    {
        return $this;
    }
}