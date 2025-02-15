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
 * @package    Bss_Gallery
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Gallery\Model\ResourceModel;

/**
 * Class Item
 *
 * @package Bss\Gallery\Model\ResourceModel
 */
class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Bss\Gallery\Model\CategoryFactory
     */
    private $categoryFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Item constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Bss\Gallery\Model\CategoryFactory $categoryFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Bss\Gallery\Model\CategoryFactory $categoryFactory,
        \Psr\Log\LoggerInterface $logger,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->date = $date;
        $this->categoryFactory = $categoryFactory;
        $this->logger = $logger;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_gallery_item', 'item_id');
    }

    /**
     * Before save item
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $categoryCollection = $this->categoryFactory->create()->getCollection()->getItems();
        $cateIds = $object->getCategoryIds();
        if ($cateIds) {
            $cateList = explode(',', $cateIds);
            foreach ($cateList as $cateId) {
                if (!array_key_exists($cateId, $categoryCollection)) {
                    unset($cateList[array_search($cateId, $cateList)]);
                }
            }
            $object->setCategoryIds(implode(',', $cateList));
        }

        if ($object->isObjectNew() && !$object->hasCreateTime()) {
            $object->setCreateTime($this->date->gmtDate());
        }

        $object->setUpdateTime($this->date->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * Before delete a item
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Exception
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $cateModel = $this->categoryFactory->create();
        if ($object->getCategoryIds()){
            foreach (explode(',', $object->getCategoryIds()) as $categoryId) {
                $category = $this->loadCategoryId($cateModel, $categoryId);
                if ($category->getData('Item_ids')){
                    $categoryItemList = explode(',', $category->getData('Item_ids'));
                    if (array_search($object->getItemId(), $categoryItemList) !== false) {
                        unset($categoryItemList[array_search($object->getItemId(), $categoryItemList)]);
                        $category->setData('Item_ids', implode(',', $categoryItemList));
                        $this->saveCategory($category);
                    }
                }
            }
        }
        return parent::_beforeDelete($object);
    }

    /**
     * Get category id
     *
     * @param \Bss\Gallery\Model\Category $cateModel
     * @param int $categoryId
     * @return \Bss\Gallery\Model\Category
     */
    private function loadCategoryId($cateModel, $categoryId)
    {
        return $cateModel->load($categoryId);
    }

    /**
     * Save a cate
     *
     * @param \Bss\Gallery\Model\Category $category
     * @return \Bss\Gallery\Model\Category
     * @throws \Exception
     */
    private function saveCategory($category)
    {
        try {
            return $category->save();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this;
        }
    }

    /**
     * Load an object using 'item_id' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = 'item_id';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param string $value
     * @param \Bss\Gallery\Model\Item $object
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $select->where(
                'is_active = ?',
                1
            )->limit(
                1
            );
        }
        return $select;
    }

    /**
     * Load by url key
     *
     * @param string $urlKey
     * @param int $isActive
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getLoadByUrlKeySelect($urlKey, $isActive = null)
    {
        $select = $this->getConnection()->select()->from(
            ['bp' => $this->getMainTable()]
        )->where(
            'bp.url_key = ?',
            $urlKey
        );

        if ($isActive !== null) {
            $select->where('bp.is_active = ?', $isActive);
        }

        return $select;
    }

    /**
     *  Check whether item url key is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericItemUrlKey(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('url_key'));
    }

    /**
     *  Check whether post url key is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidItemUrlKey(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('url_key'));
    }

    /**
     * Check url key
     *
     * @param string $urlKey
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkUrlKey($urlKey)
    {
        $select = $this->getLoadByUrlKeySelect($urlKey, 1);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS)->columns('bp.item_id')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }
}
