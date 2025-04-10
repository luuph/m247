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

use Magento\Rule\Model\ResourceModel\AbstractResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magezon\LookBook\Api\Data\ProfileInterface;
use Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class Profile extends AbstractResource
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        $this->entityManager = $entityManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    protected function _construct()
    {
        $this->_init('mgz_lookbook_profile', 'profile_id');
    }

    /**
     * Load the object
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return \Magezon\LookBook\Model\Store
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $this->getEntityManager()->load($object, $value);
        return $this;
    }

    /**
     * Process lookbook data before saving
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
                __('A Profile URL key with the same properties already exists in the selected store.')
            );
        }

        if (!$this->isValidProfileUrlKey($object) && $object->getIdentifier()) {
            throw new LocalizedException(
                __('The Profile URL key contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericProfileUrlKey($object) && $object->getIdentifier()) {
            throw new LocalizedException(
                __('The Profile URL key cannot be made of only numbers.')
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
        $entityMetadata = $this->metadataPool->getMetadata(ProfileInterface::class);
        $linkField = $entityMetadata->getLinkField();

        if ($this->storeManager->isSingleStoreMode()) {
            $stores = [Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array)$object->getData('store_id');
        }

        $select = $this->getConnection()->select()
            ->from(['cb' => $this->getMainTable()])
            ->join(
                ['cbs' => $this->getTable('mgz_lookbook_profile_store')],
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
     *  Check whether profile url key is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isValidProfileUrlKey(AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }

    /**
     *  Check whether profile url key is numeric
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isNumericProfileUrlKey(AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     * @param AbstractModel $object
     * @return \Magezon\LookBook\Model\Profile
     */
    public function save(AbstractModel $object)
    {
        $this->_beforeSave($object);
        
        $this->getEntityManager()->save($object);
        $this->_afterSave($object);
        return $this;
    }


    /**
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from(['cbs' => $this->getTable('mgz_lookbook_profile_store')], 'store_id')
            ->join(
                ['cb' => $this->getMainTable()],
                'cbs.profile_id = cb.profile_id',
                []
            )
            ->where('cb.profile_id = :profile_id');

        return $connection->fetchCol($select, ['profile_id' => (int)$id]);
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
        $this->saveProfileProducts($object);
        return parent::_afterSave($object);
    }

    /**
     * Category lookbook table name getter
     *
     * @return string
     */
    public function getProfileProductTable()
    {
        return $this->getTable('mgz_lookbook_profile_product');
    }

    /**
     * @return array
     */ 
    public function getProducts($profile)
    {
        return [];
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
    public function getCategoryIds($id)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(ProfileInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['cbs' => $this->getTable('mgz_lookbook_profile_category')], 'category_id')
            ->join(
                ['cb' => $this->getMainTable()],
                'cbs.' . $linkField . ' = cb.' . $linkField,
                []
            )
            ->where('cb.' . $entityMetadata->getIdentifierField() . ' = :profile_id');

        return $connection->fetchCol($select, ['profile_id' => (int)$id]);
    }

    /**
     * Get collection of profile categories
     *
     * @param \Magezon\LookBook\Model\Profile $profile
     * @return \Magezon\LookBook\Model\ResourceModel\Category\Collection
     */
    public function getCategoryList($profile)
    {
        /** @var \Magezon\LookBook\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['mlpc' => $this->getTable('mgz_lookbook_profile_category')],
            'main_table.category_id = mlpc.category_id',
            []
        )->where(
            'mlpc.profile_id = ?',
            (int)$profile->getId()
        )->group('main_table.category_id');
        $collection->addFieldToFilter('is_active', \Magezon\LookBook\Model\Category::STATUS_ENABLED);
        return array_values($collection->getItems());
    }

    /**
     * Get positions of associated to profile products
     *
     * @param \Magezon\LookBook\Model\Profile $profile
     * @return array
     */
    public function getProductsPosition($profile)
    {
        $select = $this->getConnection()->select()->from(
            $this->getProfileProductTable(),
            ['sku', 'position']
        )->where(
            'profile_id = :profile_id'
        );
        $bind = ['profile_id' => (int)$profile->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * Save profile products relation
     *
     * @param \Magezon\LookBook\Model\Profile $profile
     * @return \Magezon\LookBook\Model\ResourceModel\Profile
     */
    protected function saveProfileProducts($profile)
    {
        $id = $profile->getId();

        /**
         * new profile-lookbook relationships
         */
        $products = $profile->getPostedProducts();

        /**
         * Example re-save profile
         */
        if ($products === null) { 
            return $this;
        }

        /**
         * old profile-lookbook relationships
         */
        $oldProducts = $profile->getProductsPosition();
        
        $insert = array_diff_key($products, $oldProducts);
        $delete = array_diff_key($oldProducts, $products);

        /**
         * Find lookbook ids which are presented in both arrays
         * and saved before (check $oldProducts array)
         */
        $update = array_intersect_key($products, $oldProducts);
        $update = array_diff_assoc($update, $oldProducts);

        $connection = $this->getConnection();

        /**
         * Delete products from profile
         */
        if (!empty($delete)) {
            $cond = ['sku IN(?)' => array_keys($delete), 'profile_id=?' => $id];
            $connection->delete($this->getProfileProductTable(), $cond);
        }
        
        /**
         * Add products to profile
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $sku => $position) {
                $data[] = [
                    'profile_id' => (int)$id,
                    'sku'  => $sku,
                    'position'    => (int)$position
                ];
            }
        try {
            $connection->insertMultiple($this->getProfileProductTable(), $data);
        } catch(\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the profile with product.'));
        }
        
        }

        /**
         * Update lookbook positions in category
         */
        if (!empty($update)) {
            $newPositions = [];
            foreach ($update as $sku => $position) {
                $delta = $position - $oldProducts[$sku];
                if (!isset($newPositions[$delta])) {
                    $newPositions[$delta] = [];
                }
                $newPositions[$delta][] = $sku;
            }

            foreach ($newPositions as $delta => $skus) {
                $bind  = ['position' => new \Zend_Db_Expr("position + ({$delta})")];
                $where = ['profile_id = ?' => (int)$id, 'sku IN (?)' => $skus];
                $connection->update($this->getProfileProductTable(), $bind, $where);
            }
        }

        return $this;
    }
    
}
