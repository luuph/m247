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
namespace Bss\ProductTags\Model\ResourceModel\Product;

/**
 * Class Collection
 *
 * @package Bss\ProductTags\Model\ResourceModel\Product
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model.
     */
    public function _construct()
    {
        $this->_init(
            \Bss\ProductTags\Model\Product::class,
            \Bss\ProductTags\Model\ResourceModel\Product::class
        );
    }

    /**
     * Array Products id of Tag
     *
     * @param int $tagId
     * @return array
     */
    public function getProductIdsOfTag($tagId)
    {
        $productIds = [];
        $tag = $this->addFieldToSelect('*')->addFieldToFilter('protags_id', $tagId)->getData();
        foreach ($tag as $item) {
            $productIds[] = $item['product_id'];
        }
        return $productIds;
    }

    /**
     * Get product tag attribute value
     *
     * @param int $productId
     * @param int $attributeId
     * @return array
     */
    public function getProductTags($productId, $attributeId)
    {
        $columnId = 'row_id';
        $tableName = $this->getTable('catalog_product_entity_text');
        if ($this->getConnection()->tableColumnExists($tableName, 'entity_id')) {
            $columnId = 'entity_id';
        }
        $select = $this->getSelect()->reset()
            ->from(
                $tableName
            )->where(
                $columnId . ' = ?',
                $productId
            )->where(
                'attribute_id = ?',
                $attributeId
            )->where(
                'store_id = 0'
            );
        return $this->getConnection()->fetchRow($select);
    }

    /**
     * Update Product Tag
     *
     * @param array $bind
     * @param int $productId
     * @param int $attributeId
     */
    public function updateProductTags($bind, $productId, $attributeId)
    {
        $connection = $this->getConnection();
        $columnId = 'row_id';
        $tableName = $this->getTable('catalog_product_entity_text');
        if ($this->getConnection()->tableColumnExists($tableName, 'entity_id')) {
            $columnId = 'entity_id';
        }
        $connection->update(
            $tableName,
            $bind,
            [$columnId . ' = ?' => $productId, 'store_id = ?' => 0, 'attribute_id = ?' => $attributeId]
        );
    }
}
