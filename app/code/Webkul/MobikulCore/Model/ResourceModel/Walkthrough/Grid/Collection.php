<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\ResourceModel\Walkthrough\Grid;

use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Webkul\MobikulCore\Model\ResourceModel\Walkthrough\Collection as WalkthroughCollection;

/**
 * Class Collection grid
 */
class Collection extends WalkthroughCollection implements SearchResultInterface
{
    /**
     * Aggregations variable
     *
     * @var mixed
     */
    protected $_aggregations;

    /**
     * ObjectManager variable
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Construct function
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param [type] $mainTable
     * @param [type] $eventPrefix
     * @param [type] $eventObject
     * @param [type] $resourceModel
     * @param [type] $model
     * @param [type] $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * GetAggregations function
     *
     * @return void
     */
    public function getAggregations()
    {
        return $this->_aggregations;
    }

    /**
     * SetAggregations function
     *
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return void
     */
    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
    }

    /**
     * GetAllIds function
     *
     * @param int $limit
     * @param int $offset
     * @return void
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * GetSearchCriteria function
     *
     * @return void
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * SetSearchCriteria function
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return void
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * GetTotalCount function
     *
     * @return void
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * SetTotalCount function
     *
     * @param int $totalCount
     * @return void
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * SetItems function
     *
     * @param array|null $items
     * @return void
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
