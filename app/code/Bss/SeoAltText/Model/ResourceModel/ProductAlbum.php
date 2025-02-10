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
 * @package    Bss_SeoAltText
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoAltText\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ProductAlbum
 * @package Bss\SeoAltText\Model\ResourceModel
 */
class ProductAlbum extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('catalog_product_entity_varchar', 'value_id');
    }

    /**
     * @param string $oldPath
     * @param string $newPath
     * @return $this
     */
    public function updateValue($oldPath, $newPath)
    {
        $connection = $this->getConnection();
        $where = ['value=?' => $oldPath];
        $entityVarcharTable = $this->getTable('catalog_product_entity_varchar');
        $mediaGalleryTable = $this->getTable('catalog_product_entity_media_gallery');
        $dataUpdate = ['value' => $newPath];
        $connection->update($entityVarcharTable, $dataUpdate, $where);
        $connection->update($mediaGalleryTable, $dataUpdate, $where);
        return $this;
    }

    /**
     * Save multiple data alt tag in database.
     *
     * @param array $dataChange
     * @param array $attributeAlt
     * @param int $storeId
     * @return void
     */
    public function saveMultipleData($dataChange, $attributeAlt, $storeId = 0)
    {
        /* Duplicate data for store view if this is not exits */
        $this->getDataGallery($dataChange, $storeId);
        $this->getDataAttributeImage($dataChange, $storeId, $attributeAlt);

        $connection = $this->getConnection();
        $conditions = [];

        foreach ($dataChange as $productId => $alt) {
            $case = $connection->quoteInto('?', $productId);
            $result = $connection->quoteInto('?', $alt);
            $conditions[$case] = $result;
        }

        $value = $connection->getCaseSql('entity_id', $conditions);

        $whereTBVarchar = [
            'entity_id IN (?)' => array_keys($dataChange),
            'attribute_id IN (?)' => $attributeAlt,
            'store_id = ?' => $storeId
        ];
        $whereTBMedia = [
            'entity_id IN (?)' => array_keys($dataChange),
            'store_id = ?' => $storeId
        ];

        try {
            $connection->beginTransaction();
            $connection->update($this->getTable('catalog_product_entity_varchar'), ['value' => $value], $whereTBVarchar);
            $connection->update($this->getTable('catalog_product_entity_media_gallery_value'), ['label' => $value], $whereTBMedia);
            $connection->commit();
        } catch(\Exception $e) {
            $connection->rollBack();
        }
    }

    /**
     * Get all data gallery in store 0 and $storeId
     *
     * @param array $dataChange
     * @param int $storeId
     * @return void
     */
    public function getDataGallery($dataChange, $storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('catalog_product_entity_media_gallery_value'))
            ->where('store_id IN (?)', [0, $storeId])
            ->where('entity_id IN (?)', array_keys($dataChange))
            ->order('store_id DESC');
        $results = $connection->fetchAll($select);

        $this->duplicateForStore($results, $storeId, 'catalog_product_entity_media_gallery_value');
    }

    /**
     * Get all data attribute Image in store 0 and $storeId
     *
     * @param array $dataChange
     * @param int $storeId
     * @param array $attributeAlt
     * @return void
     */
    public function getDataAttributeImage($dataChange, $storeId, $attributeAlt)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('catalog_product_entity_varchar'))
            ->where('store_id IN (?)', [0, $storeId])
            ->where('entity_id IN (?)', array_keys($dataChange))
            ->where('attribute_id IN (?)', $attributeAlt)
            ->order('store_id DESC');
        $results = $connection->fetchAll($select);

        $this->duplicateForStore($results, $storeId, 'catalog_product_entity_varchar');
    }

    /**
     * Insert data if this exits in store 0 but not exits in $storeId
     *
     * @param array $dataBase
     * @param int $storeId
     * @param string $tableName
     * @return void
     */
    public function duplicateForStore($dataBase, $storeId, $tableName)
    {
        $skipInsert = [];
        $dataInsert = [];

        foreach ($dataBase as $item) {
            $id = $item['entity_id'];
            if (isset($item['attribute_id'])) {
                $id .= "-" . $item['attribute_id'];
            }

            if ($item['store_id'] == $storeId) { // Skip because the record already exists at the store_id.
                $skipInsert[] = $id;
                continue;
            }

            /* Duplicate data */
            if (isset($item['record_id'])) {
                $item['record_id'] = null; // Primary key
            } else {
                $item['value_id'] = null; // Primary key
            }
            $item['store_id'] = $storeId; // Replace store_id 0 to $storeId
            $dataInsert[$id] = $item;
        }

        $skipInsert = array_unique($skipInsert);
        foreach ($skipInsert as $id) {
            if (isset($dataInsert[$id])) {
                unset($dataInsert[$id]);
            }
        }

        if (!$dataInsert) {
            return;
        }

        $connection = $this->getConnection();
        $connection->insertMultiple($this->getTable($tableName), $dataInsert);
    }
}
