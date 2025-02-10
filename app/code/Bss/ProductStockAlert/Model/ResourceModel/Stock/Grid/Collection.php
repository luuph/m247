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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Model\ResourceModel\Stock\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface;

class Collection extends \Bss\ProductStockAlert\Model\ResourceModel\Stock\Collection implements \Magento\Framework\Api\Search\SearchResultInterface
{

    /**
     * Model
     *
     * @var string
     */
    protected $model;

    /**
     * Event Prefix
     *
     * @var AbstractDb
     */
    protected $eventPrefix;

    /**
     * Event Object
     *
     * @var string
     */
    protected $eventObject;

    /**
     * Aggregations
     *
     * @var \Magento\Framework\Api\Search\AggregationInterface
     */
    private $aggregations;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $attribute;

    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * Collection constructor.
     *
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $attribute
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param string $eventPrefix
     * @param string $eventObject
     * @param string $model
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $attribute,
        EntityFactoryInterface                         $entityFactory,
        LoggerInterface                                $logger,
        FetchStrategyInterface                         $fetchStrategy,
        ManagerInterface                               $eventManager,
                                                       $eventPrefix,
                                                       $eventObject,
                                                       $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        AbstractDb                                     $resource = null
    ) {
        $this->helper = $helper;
        $this->attribute = $attribute;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->eventPrefix = $eventPrefix;
        $this->eventObject = $eventObject;
        $this->model = $model;
    }

    /**
     * Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \Bss\ProductStockAlert\Model\ResourceModel\Stock::class
        );
    }

    /**
     * Get short and full content
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFilterToMap('product_name', 'catalog_product_entity_varchar.value');
        $attribute = $this->attribute->getIdByCode ('catalog_product', 'name');
        if ($this->helper->isEnterpriseEdition()) {
            $this->joinCollectionEE();
        } else {
            $this->joinCollectionCE();
        }
        $this->getSelect()
            ->where('catalog_product_entity_varchar.attribute_id = ?', $attribute)
            ->where('catalog_product_entity_varchar.store_id = 0');
        return $this;
    }

    /**
     * Use for magento CE
     *
     * @return void
     */
    public function joinCollectionCE()
    {
        $this->getSelect()
            ->joinLeft(
                ['catalog_product_entity_varchar' => $this->getTable('catalog_product_entity_varchar')],
                'main_table.product_id = catalog_product_entity_varchar.entity_id',
                ['product_name' => 'catalog_product_entity_varchar.value']
            );
    }

    /**
     * User for magento EE
     *
     * @return void
     */
    public function joinCollectionEE()
    {
        $this->getSelect()
            ->joinLeft(
                ['catalog_product_entity' => $this->getTable('catalog_product_entity')],
                "main_table.product_id = catalog_product_entity.entity_id"
            )
            ->joinLeft(
                ['catalog_product_entity_varchar' => $this->getTable('catalog_product_entity_varchar')],
                "catalog_product_entity.row_id = catalog_product_entity_varchar.row_id",
                ['product_name' => 'catalog_product_entity_varchar.value']
            );
    }

    /**
     * Get Aggregations
     *
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Set Aggregations
     *
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return void
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param array|null $items
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Get Items
     *
     * @return \Magento\Framework\Api\Search\DocumentInterface[]|\Magento\Framework\DataObject[]
     */
    public function getItems()
    {
        return parent::getItems();
    }
}
