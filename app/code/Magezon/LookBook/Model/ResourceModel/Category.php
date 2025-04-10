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

namespace Magezon\LookBook\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magezon\LookBook\Api\Data\CategoryInterface;
use Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory as ProfileCollectionFactory;

class Category extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ProfileCollectionFactory
     */
    protected $profileCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context                $context
     * @param string                 $connectionName
     * @param StoreManagerInterface  $storeManager
     * @param MetadataPool           $metadataPool
     * @param ProfileCollectionFactory $profileCollectionFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        ProfileCollectionFactory $profileCollectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        $this->profileCollectionFactory = $profileCollectionFactory;
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mgz_lookbook_category', 'category_id');
    }

    /**
     * Load an object
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return \Magezon\LookBook\Model\ResourceModel\Category
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $this->getEntityManager()->load($object, $value);
        return $this;
    }

    /**
     * Process category data before saving
     *
     * @param AbstractModel $object
     * @return $this
     */
    public function _beforeSave(AbstractModel $object)
    {
        $urlKey = $object->getIdentifier();
        if ($urlKey) {
            $object->setIdentifier(strtolower($urlKey));
        }

        if (!$object->getId() && !$object->getIdentifier() && $object->getTitle()) {
            $urlKey = $object->formatUrlKey($object->getTitle());
            $object->setIdentifier($urlKey);
        }

        if ($urlKey = $object->getIdentifier()) {
            $object->setIdentifier($object->formatUrlKey($urlKey));
        }

        if (!$this->checkIsUniqueUrl($object)) {
            throw new LocalizedException(
                __('A Category URL key with the same properties already exists in the selected store.')
            );
        }

        if (!$this->isValidCategoryUrlKey($object) && $object->getIdentifier()) {
            throw new LocalizedException(
                __('The Category URL key contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericCategoryUrlKey($object) && $object->getIdentifier()) {
            throw new LocalizedException(
                __('The Category URL key cannot be made of only numbers.')
            );
        }

        return $this;
    }

    /**
     * Check for unique of identifier of block to selected store(s).
     *
     * @param AbstractModel $object
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function checkIsUniqueUrl(AbstractModel $object)
    {
        $entityMetadata = $this->metadataPool->getMetadata(CategoryInterface::class);
        $linkField = $entityMetadata->getLinkField();

        if ($this->storeManager->isSingleStoreMode()) {
            $stores = [Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array)$object->getData('store_id');
        }

        $select = $this->getConnection()->select()
            ->from(['cb' => $this->getMainTable()])
            ->join(
                ['cbs' => $this->getTable('mgz_lookbook_category_store')],
                'cb.' . $linkField . ' = cbs.' . $linkField,
                []
            )
            ->where('cb.identifier = ?', $object->getData('identifier'))
            ->where('cbs.store_id IN (?)', $stores);

        if ($object->getId()) {
            $select->where('cb.' . $entityMetadata->getIdentifierField() . ' <> ?', $object->getId());
        }

        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     *  Check whether category url key is numeric
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isNumericCategoryUrlKey(AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether category url key is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isValidCategoryUrlKey(AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function save(AbstractModel $object)
    {
        $this->_beforeSave($object);
        $this->getEntityManager()->save($object);
        $this->_afterSave($object);
        return $this;
    }

    /**
     * Process category data after save category object
     * save related profiles ids
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->_saveCategoryProfiles($object);
        return parent::_afterSave($object);
    }

    /**
     * Delete the object
     *
     * @param AbstractModel $object
     * @return $this
     */
    public function delete(AbstractModel $object)
    {
        $this->getEntityManager()->delete($object);
        return $this;
    }

    /**
     * @return \Magento\Framework\EntityManager\EntityManager
     * @deprecated 100.1.0
     */
    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->entityManager = ObjectManager::getInstance()
                ->get(EntityManager::class);
        }
        return $this->entityManager;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(CategoryInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['cbs' => $this->getTable('mgz_lookbook_category_store')], 'store_id')
            ->join(
                ['cb' => $this->getMainTable()],
                'cbs.' . $linkField . ' = cb.' . $linkField,
                []
            )
            ->where('cb.' . $entityMetadata->getIdentifierField() . ' = :category_id');

        return $connection->fetchCol($select, ['category_id' => (int)$id]);
    }

    /**
     * Get collection of category profiles
     *
     * @param \Magezon\LookBook\Model\Category $category
     * @return \Magezon\LookBook\Model\ResourceModel\Profile\Collection
     */
    public function getProfileCollection($category)
    {
        /** @var \Magezon\LookBook\Model\ResourceModel\Profile\Collection $collection */
        $collection = $this->profileCollectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['mlpc' => $this->getTable('mgz_lookbook_profile_category')],
            'main_table.profile_id = mlpc.profile_id',
            []
        )->group('main_table.profile_id');
        $collection->prepareCollection();
        $collection->addCategoryCollection();
        $collection->addFieldToFilter(
            'mlpc.category_id',
            (int)$category->getId()
        );
        return $collection;
    }

    /**
     * Category lookbook table name getter
     *
     * @return string
     */
    public function getCategoryProfileTable()
    {
        return $this->getTable('mgz_lookbook_profile_category');
    }

    /**
     * Get positions of associated to category profiles
     *
     * @param \Magezon\LookBook\Model\Category $category
     * @return array
     */
    public function getProfilesPosition($category)
    {
        $select = $this->getConnection()->select()->from(
            $this->getCategoryProfileTable(),
            ['profile_id', 'position']
        )->where(
            'category_id = :category_id'
        );
        $bind = ['category_id' => (int)$category->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * Save category profiles relation
     *
     * @param \Magezon\LookBook\Model\Category $category
     * @return \Magezon\LookBook\Model\ResourceModel\Category
     */
    protected function _saveCategoryProfiles($category)
    {
        $id = $category->getId();

        /**
         * new category-lookbook relationships
         */
        $profiles = $category->getPostedProfiles();

        /**
         * Example re-save category
         */
        if ($profiles === null) {
            return $this;
        }

        /**
         * old category-lookbook relationships
         */
        $oldProfiles = $category->getProfilesPosition();

        $insert = array_diff_key($profiles, $oldProfiles);
        $delete = array_diff_key($oldProfiles, $profiles);

        /**
         * Find lookbook ids which are presented in both arrays
         * and saved before (check $oldProfiles array)
         */
        $update = array_intersect_key($profiles, $oldProfiles);
        $update = array_diff_assoc($update, $oldProfiles);

        $connection = $this->getConnection();

        /**
         * Delete profiles from category
         */
        if (!empty($delete)) {
            $cond = ['profile_id IN(?)' => array_keys($delete), 'category_id=?' => $id];
            $connection->delete($this->getCategoryProfileTable(), $cond);
        }

        /**
         * Add profiles to category
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $profileId => $position) {
                $data[] = [
                    'category_id' => (int)$id,
                    'profile_id'  => (int)$profileId,
                    'position'    => (int)$position
                ];
            }
            $connection->insertMultiple($this->getCategoryProfileTable(), $data);
        }

        /**
         * Update lookbook positions in category
         */
        if (!empty($update)) {
            $newPositions = [];
            foreach ($update as $profileId => $position) {
                $delta = $position - $oldProfiles[$profileId];
                if (!isset($newPositions[$delta])) {
                    $newPositions[$delta] = [];
                }
                $newPositions[$delta][] = $profileId;
            }

            foreach ($newPositions as $delta => $profileIds) {
                $bind  = ['position' => new \Zend_Db_Expr("position + ({$delta})")];
                $where = ['category_id = ?' => (int)$id, 'profile_id IN (?)' => $profileIds];
                $connection->update($this->getCategoryProfileTable(), $bind, $where);
            }
        }

        return $this;
    }

    /**
     * Get profiles count in category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return int
     */
    public function getProfileCount($category)
    {
        return $this->getProfileCollection($category)->count();
    }
}
