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
 * @category  BSS
 * @package   Bss_ProductTags
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Protags extends AbstractDb
{
    /**
     * @var \Bss\ProductTags\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     * Protags constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Bss\ProductTags\Model\ProductFactory $productFactory
     * @param string $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Backend\Helper\Js $jsHelper,
        \Bss\ProductTags\Model\ProductFactory $productFactory,
        $resourcePrefix = null
    ) {
        $this->productFactory = $productFactory;
        $this->jsHelper = $jsHelper;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {

        $this->_init('bss_protags_rule', 'protags_id');
    }

    /**
     * After Save
     *
     * @param AbstractModel $object
     * @return AbstractDb
     */
    public function _afterSave(AbstractModel $object)
    {
        $this->saveProducts($object)
             ->saveStoreId($object)
             ->saveNameTag($object)
             ->saveTagKey($object);

        return parent::_afterSave($object);
    }

    /**
     * After load
     *
     * @param AbstractModel $object
     * @return AbstractDb
     */
    public function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $name   = $this->lookupOldNameTag($object->getId());
            $names  = implode(",", $name);
            $key =  $this->lookupOldKeyTag($object->getId());
            $stores = $this->lookupOldStoreIds($object->getId());
            $object->setData('name_tag', $names);
            $object->setData('store_id', $stores);
            $object->setData('tag_key', $key);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Lookup old store id
     *
     * @param string $tagId
     * @return array
     */
    private function lookupOldStoreIds($tagId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('bss_protags_rule_store'),
            'store_id'
        )->where(
            'protags_id = ?',
            (int)$tagId
        );
        return $connection->fetchCol($select);
    }

    /**
     * Look up old name tag
     *
     * @param string $tagId
     * @return array
     */
    private function lookupOldNameTag($tagId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('bss_protags_tag'),
            'name_tag'
        )->where(
            'protags_id = ?',
            (int)$tagId
        );
        return $connection->fetchCol($select);
    }

    /**
     * Lookup old Tag key
     *
     * @param string $tagId
     * @return array
     */
    private function lookupOldKeyTag($tagId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('bss_protags_rule_key'),
            'tag_key'
        )->where(
            'protags_id = ?',
            (int)$tagId
        );
        return $connection->fetchCol($select);
    }

    /**
     * Save product
     *
     * @param object $model
     * @return $this
     */
    private function saveProducts($model)
    {
        $dataModel = $model->getData();
        if (isset($dataModel['products'])) {
            $listProductIds = $dataModel['bss_input'] ?? $dataModel['products'];
            $productIds = $this->jsHelper->decodeGridSerializedInput($listProductIds);
            $oldProducts = [];
            $collection = $this->productFactory->create()->getCollection()
                ->addFieldToSelect('product_id')
                ->addFieldToFilter('protags_id', $model->getId());
            foreach ($collection as $col) {
                $oldProducts[] = $col->getProductId();
            }
            $newProducts = (array) $productIds;
            $table = $this->getTable('bss_protags_rule_product');
            $insert = array_diff($newProducts, $oldProducts);
            $delete = array_diff($oldProducts, $newProducts);

            if ($delete) {
                $where = ['protags_id = ?' => (int)$model->getId(), 'product_id IN (?)' => $delete];
                $this->getConnection()->delete($table, $where);
            }

            if ($insert) {
                $data = [];
                foreach ($insert as $product_id) {
                    $data[] = ['protags_id' => (int)$model->getId(), 'product_id' => (int)$product_id];
                }
                $this->getConnection()->insertMultiple($table, $data);
            }
        }
        return $this;
    }

    /**
     * Save Store Id
     *
     * @param object $model
     * @return $this
     */
    private function saveStoreId($model)
    {
        $newStoreIds = (array) $model->getStores();
        if ($newStoreIds) {
            $oldStoreIds = $this->lookupOldStoreIds($model->getId());

            $table = $this->getTable('bss_protags_rule_store');
            $insert = array_diff($newStoreIds, $oldStoreIds);
            $delete = array_diff($oldStoreIds, $newStoreIds);

            if ($delete) {
                $where = ['protags_id = ?' => (int)$model->getId(), 'store_id IN (?)' => $delete];
                $this->getConnection()->delete($table, $where);
            }

            if ($insert) {
                $data = [];
                foreach ($insert as $store_id) {
                    $data[] = ['protags_id' => (int)$model->getId(), 'store_id' => (int)$store_id];
                }
                $this->getConnection()->insertMultiple($table, $data);
            }
        }
        return $this;
    }

    /**
     * Save Name Tag
     *
     * @param object $model
     * @return $this
     */
    private function saveNameTag($model)
    {
        $nameTags = $model->getNameTag();
        if ($nameTags) {
            if (!is_array($nameTags)) {
                $nameTags = array_unique(explode(",", $nameTags));
            }
            $oldNameTags = $this->lookupOldNameTag($model->getId());

            $table = $this->getTable('bss_protags_tag');
            $insert = array_diff($nameTags, $oldNameTags);
            $delete = array_diff($oldNameTags, $nameTags);

            if ($delete) {
                $where = ['protags_id = ?' => (int)$model->getId(), 'name_tag IN (?)' => $delete];
                $this->getConnection()->delete($table, $where);
            }

            if ($insert) {
                $data = [];
                foreach ($insert as $name_tag) {
                    $data[] = ['protags_id' => (int)$model->getId(), 'name_tag' => $name_tag];
                }
                $this->getConnection()->insertMultiple($table, $data);
            }
        }
        return $this;
    }

    /**
     * Save Tag Key
     *
     * @param object $model
     * @return $this
     */
    private function saveTagKey($model)
    {
        $tagKey = $model->getTagKey();
        if ($tagKey) {
            $stringKey = preg_replace('/\s*,\s*/', ',', $tagKey);
            $tagKey = array_unique(explode(",", $stringKey));
            $oldKeyTag = $this->lookupOldKeyTag($model->getId());
            $table = $this->getTable('bss_protags_rule_key');
            $insert = array_diff($tagKey, $oldKeyTag);
            $delete = array_diff($oldKeyTag, $tagKey);

            if ($delete) {
                $where = ['protags_id = ?' => (int)$model->getId(), 'tag_key IN (?)' => $delete];
                $this->getConnection()->delete($table, $where);
            }

            if ($insert) {
                $data = [];
                foreach ($insert as $tag_key) {
                    $data[] = ['protags_id' => (int)$model->getId(), 'tag_key' => $tag_key];
                }
                $this->getConnection()->insertMultiple($table, $data);
            }
        }
        return$this;
    }
}
