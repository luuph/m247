<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\LookBook\Model\ResourceModel\Profile;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'profile_id';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var boolean
     */
    protected $addCategoryCollection;

    protected $currentStoreId;

    /**
     * Collection constructor.
     * @param EntityFactoryInterface $entityFactory
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        CategoryCollectionFactory $categoryCollectionFactory,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    protected function _construct()
    {
        $this->_init(\Magezon\LookBook\Model\Profile::class, \Magezon\LookBook\Model\ResourceModel\Profile::class);
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinTable('store_table', 'mgz_lookbook_profile_store', 'profile_id');
    }

    /**
     * @param $alias
     * @param $tableName
     * @param $linkField
     */
    protected function joinTable($alias, $tableName, $linkField)
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->joinLeft(
                [$alias => $this->getTable($tableName)],
                'main_table.' . $linkField . ' = ' . $alias . '.' . $linkField,
                []
            )->group(
                'main_table.' . $linkField
            );
        }
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('mgz_lookbook_profile_store', 'profile_id', 'store_id');
        if ($this->addCategoryCollection) {
            $ids        = $this->getAllIds();
            $table      = $this->getResource()->getTable('mgz_lookbook_profile_category');
            $connection = $this->getResource()->getConnection();
            $select     = $connection->select()->from($table)->where('profile_id IN (?)', $ids);
            $list       = $connection->fetchAll($select);
            $collection = $this->categoryCollectionFactory->create();
            $collection->getSelect()->joinLeft(
                ['miac' => $this->getResource()->getTable('mgz_lookbook_profile_category')],
                'main_table.category_id = miac.category_id',
                []
            )->where(
                'miac.profile_id IN (?)',
                $ids
            )->group('main_table.category_id');
            $collection->addFieldToFilter('is_active', \Magezon\LookBook\Model\Category::STATUS_ENABLED);

            foreach ($this as &$item) {
                $_categories = [];
                foreach ($list as $_row) {
                    if (($_row['profile_id'] == $item->getId()) && ($_category = $collection->getItemById($_row['category_id']))) {
                        $_categories[] = $_category;
                    }
                }
                $item->setCategoryList($_categories);
                $item->setCategoryIds($_categories);
            }
        }
        return parent::_afterLoad(); // TODO: Change the autogenerated stub
    }

    /**
     * @return Collection|void
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('profile_id', 'main_table.profile_id');
        return parent::_initSelect(); // TODO: Change the autogenerated stub
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function performAfterLoad($tableName, $linkField, $field)
    {
        $id = $this->getColumnValues($linkField);
        if (count($id)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['customer_data' => $this->getTable($tableName)])
                ->where('customer_data.' . $linkField . ' IN (?)', $id);
            $result = $connection->fetchAll($select);
            if ($result) {
                $storesData = [];
                foreach ($result as $storeData) {
                    $storesData[$storeData[$linkField]][] = $storeData[$field];
                }
                foreach ($this as $item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($storesData[$linkedId])) {
                        continue;
                    }
                    $item->setData($field, $storesData[$linkedId]);
                }
            }
        }
    }

    /**
     * Add field filter to collection
     *
     * @param array|string $field
     * @param string|int|array|null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            if (isset($condition['eq'])) {
                $this->currentStoreId = $condition['eq'];
            }
            return $this->addStoreFilter($condition, false);
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return \Magezon\LookBook\Model\ResourceModel\Profile\Collection
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        $this->performAddStoreFilter($store, $withAdmin);
        return $this;
    }

    /**
     * Perform adding filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('store', ['in' => $store], 'public');
    }

    /**
     * @return \Magezon\LookBook\Model\ResourceModel\Profile\Collection
     */
    public function addCategoryCollection()
    {
        $this->addCategoryCollection = true;
        return $this;
    }

    public function prepareCollection($storeId = null)
    {
        $store = $this->storeManager->getStore($storeId);
        $this->addIsActiveFilter()
            ->addStoreFilter($store)
            ->addCategoryCollection();
        $this->getSelect()->where('main_table.creation_time <= now()'); 
        return $this; 
    }

    /**
     * Filter collection to only active or inactive rules
     *
     * @param int $isActive
     * @return \Magezon\LookBook\Model\ResourceModel\Profile\Collection
     */
    public function addIsActiveFilter($isActive = 1)
    {
        if (!$this->getFlag('is_active_filter')) {
            $this->addFieldToFilter('main_table.is_active', 1);
            $this->setFlag('is_active_filter', true);
        }
        return $this;
    }
}
