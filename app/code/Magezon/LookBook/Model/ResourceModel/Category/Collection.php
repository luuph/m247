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

namespace Magezon\LookBook\Model\ResourceModel\Category;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magezon\LookBook\Api\Data\CategoryInterface;
use Magezon\LookBook\Model\Category;
use Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory as ProfileCollectionFactory;

class Collection extends \Magezon\LookBook\Model\ResourceModel\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'category_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'lookbook_category_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'category_collection';

    /**
     * @var boolean
     */
    protected $addListProfiles;

    /**
     * @var ProfileCollectionFactory
     */
    protected $profileCollectionFactory;

    /**
     * [__construct description]
     * @param FetchStrategyInterface $fetchStrategy
     * @param EntityFactoryInterface $entityFactory
     * @param AdapterInterface|null  $connection
     * @param MetadataPool           $metadataPool
     * @param ManagerInterface       $eventManager
     * @param AbstractDb|null        $resource
     * @param StoreManagerInterface  $storeManager
     * @param LoggerInterface        $logger
     * @param ProfileCollectionFactory $profileCollectionFactory
     */
    public function __construct(
        FetchStrategyInterface $fetchStrategy,
        EntityFactoryInterface $entityFactory,
        MetadataPool $metadataPool,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        ProfileCollectionFactory $profileCollectionFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        
        parent::__construct($fetchStrategy, $entityFactory, $metadataPool, $eventManager, $storeManager, $logger, $connection, $resource);
        $this->profileCollectionFactory = $profileCollectionFactory;
    }

    protected function _construct()
    {
        $this->_init(Category::class, \Magezon\LookBook\Model\ResourceModel\Category::class);
        $this->_map['fields']['category_id'] = 'main_table.category_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $entityMetadata = $this->metadataPool->getMetadata(CategoryInterface::class);
        $this->performAfterLoad('mgz_lookbook_category_store', $entityMetadata->getLinkField());

        if ($this->addListProfiles) {
            $ids        = $this->getAllIds();
            $table      = $this->getResource()->getTable('mgz_lookbook_profile_category');
            $connection = $this->getResource()->getConnection();
            $select     = $connection->select()->from($table)->where('category_id IN (?)', $ids);
            $list       = $connection->fetchAll($select);
            $collection = $this->profileCollectionFactory->create();
            $collection->prepareCollection();

            foreach ($this as &$category) {
                $count = 0;
                foreach ($list as $row) {
                    if ($row['category_id'] == $category->getId() && $collection->getItemById($row['profile_id'])) {
                        $count++;
                    }
                }
                $category->setTotalProfiles($count);   
            }
        }

        return parent::_afterLoad();
    }

    public function prepareCollection($storeId = null)
    {
        $store = $this->storeManager->getStore($storeId);
        $this->addIsActiveFilter()
            ->addStoreFilter($store);
        return $this;
    }

    /**
     * Filter collection to only active or inactive rules
     *
     * @param int $isActive 
     * @return $this
     */
    public function addIsActiveFilter($isActive = 1)
    {
        if (!$this->getFlag('is_active_filter')) {
            $this->addFieldToFilter('is_active', 1);
            $this->setFlag('is_active_filter', true);
        }
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('mgz_lookbook_category_store', 'category_id');
    }

    /**
     * @return $this
     */
    public function addTotalProfiles()
    {
        $this->addListProfiles = true;
        return $this;
    }
}
