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
 * @package    Bss_ProductTags
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ProductTags\Model\ResourceModel\ProTags\Indexer;

use Bss\ProductTags\Helper\Data;
use Bss\ProductTags\Model\ResourceModel\ProductTagName;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Bss\ProductTags\Model\ResourceModel\ProTags\Collection
{
    const STATUS_TAG_PRODUCT = '1';
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $eavAttribute;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ProductTagName
     */
    protected $productTagName;

    /**
     * @var ProductTagName\Collection
     */
    protected $collectionTagName;

    /**
     * Collection constructor.
     * @param ProductTagName\Collection $collectionTagName
     * @param ProductTagName $productTagName
     * @param Data $helper
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $eavAttribute
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Bss\ProductTags\Model\ResourceModel\ProductTagName\Collection $collectionTagName,
        ProductTagName $productTagName,
        Data $helper,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filter\FilterManager $filter,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttribute,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $connection,
            $resource
        );
        $this->filter = $filter;
        $this->eavAttribute = $eavAttribute;
        $this->helper = $helper;
        $this->productTagName = $productTagName;
        $this->collectionTagName = $collectionTagName;
    }

    /**
     * Join table data
     *
     * @param string $tag
     * @param array $meta
     * @throws NoSuchEntityException
     * @throws \Zend_Db_Exception
     */
    public function getJoinTableData($tag, $meta = [])
    {
        $this->createTemporaryTableToIndex();
        $table = $this->getConnection()->getTableName('bss_protags_product_tagname_index_temp');
        $select = $this->joinTableRuleProtag();
        $sql = $this->getConnection()->query($select);
        $data = $allStore = [];
        foreach ($sql as $value) {
            if ($value['store_id'] == 0) {
                $allStore[] = $value;
                $storeId = $this->getStoreId();
                foreach ($storeId as $col) {
                    $value['store_id'] = $col;
                    $value['status'] = '1';
                    $data[] = $value;
                }
            }
            $value['status'] = '1';
            $data[] = $value;
        }
        $status = \Bss\ProductTags\Helper\Data::DISABLE_META_TAG;
        if ($this->helper->getConfig('general/use_meta_keyword')) {
            $status = \Bss\ProductTags\Helper\Data::ENABLE_META_TAG;
        }
        $dataProductTag = $this->convertArray((array)$tag, self::STATUS_TAG_PRODUCT);
        $dataProductMetakey = $this->convertArray($meta, $status);
        $dataMerge = array_merge($dataProductTag, $dataProductMetakey);

        $dataMergeAfter = $this->doMergeAttrDataWithOriData($data, $dataMerge);

        foreach (array_chunk($dataMergeAfter, 1000) as $item) {
            $this->getConnection()->insertMultiple($table, $item);
        }
        $this->getProductTagAllStore($allStore);
    }

    /**
     * Process tag for all store
     *
     * @param array $allStore
     * @throws NoSuchEntityException
     */
    private function getProductTagAllStore($allStore)
    {
        $products = [];
        if (is_array($allStore) && !empty($allStore)) {
            foreach ($allStore as $value) {
                if (isset($value['product_id'])) {
                    $products[$value['product_id']][] = $value['tag'];
                }
            }
        }
        if (!empty($products)) {
            $attribute = $this->eavAttribute->get(\Magento\Catalog\Model\Product::ENTITY, 'product_tag');
            $attributeId = $attribute->getAttributeId();
            foreach ($products as $productId => $tag) {
                $existTag = $this->getTagExistProduct($productId, $attributeId);
                if ($existTag) {
                    $products[$productId] = array_merge($products[$productId], $existTag);
                }
            }
            foreach ($products as $productId => $productTag) {
                $productTag = array_unique($productTag);
                $tag = implode(",", $productTag);
                $this->processTagForProduct($productId, $tag, $attributeId);
            }
        }
    }

    /**
     * Get tag exist product.
     *
     * @param int $productId
     * @param int $attributeId
     * @return array|bool
     */
    private function getTagExistProduct($productId, $attributeId)
    {
        $columnId = 'row_id';
        $tableName = $this->getTable('catalog_product_entity_text');
        if ($this->getConnection()->tableColumnExists($tableName, 'entity_id')) {
            $columnId = 'entity_id';
        }
        $select = $this->getSelect()->reset()
            ->from(
                $this->getTable('catalog_product_entity_text')
            )->where(
                $columnId . ' = ?',
                $productId
            )->where(
                'attribute_id = ?',
                $attributeId
            )->where(
                'store_id = 0'
            );
        $data = $this->getConnection()->fetchRow($select);
        if (is_array($data) && !empty($data)) {
            $data['value'] = isset($data['value']) ? explode(",", $data['value']) : [];
            $data['value'] = array_unique($data['value']);
            return $data['value'];
        }
        return false;
    }

    /**
     * Process tag for product
     *
     * @param int $productId
     * @param string $tag
     * @param int $attributeId
     */
    private function processTagForProduct($productId, $tag, $attributeId)
    {
        try {
            $columnId = 'row_id';
            $tableName = $this->getTable('catalog_product_entity_text');
            if ($this->_resource->getConnection()->tableColumnExists($tableName, 'entity_id')) {
                $columnId = 'entity_id';
            }
            $this->deleteTagProduct($productId, $attributeId);
            $connection = $this->getConnection();
            $bind = [
                'attribute_id' => $attributeId,
                'store_id' => 0,
                'value' => $tag,
                $columnId => $productId
            ];
            $connection->insert($this->getTable($tableName), $bind);
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
        }
    }

    /**
     * Delete tag
     *
     * @param int $productId
     * @param int $attributeId
     */
    private function deleteTagProduct($productId, $attributeId)
    {
        try {
            $columnId = 'row_id';
            if ($this->_resource->getConnection()->tableColumnExists('catalog_product_entity_text', 'entity_id')) {
                $columnId = 'entity_id';
            }
            $connection = $this->getConnection();
            $connection->delete(
                $this->getTable('catalog_product_entity_text'),
                [$columnId . ' = ?' => $productId, 'store_id = ?' => 0, 'attribute_id = ?' => $attributeId]
            );
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
        }
    }

    /**
     * Set Data to table bss_protags_product_tagname_index
     *
     * @param string $tag
     * @param array $id
     * @param array $meta
     * @throws \Zend_Db_Exception|NoSuchEntityException
     * @codingStandardsIgnoreStart
     */
    public function setDataToTableIndex($tag, $id = [], $meta = [])
    {
        $tableIndex = $this->getTable('bss_protags_product_tagname_index');
        $this->getJoinTableData($tag, $meta);
        $table = $this->getConnection()->getTableName('bss_protags_product_tagname_index_temp');
        $select = $this->getConnection()
            ->select()
            ->from($table)
            ->distinct(true);
        if ($id) {
            $select->where('product_id IN (?)', $id);
        }
        $sql = $this->getConnection()->query($select);
        $data = [];
        foreach ($sql as $value) {
            $data[] = $value;
        }
        foreach (array_chunk($data, 1000) as $item) {
            $this->getConnection()->insertMultiple($tableIndex, $item);
        }
        $this->getConnection()->dropTable($table);
    }

    /**
     * @param array$array
     * @param string $status
     * @return array
     */
    private function convertArray($array, $status)
    {
        $nameTags = $this->collectionTagName->arrayNameTag();
        $storeId = $this->getStoreId();
        $data = [];
        foreach ($array as $key => $value) {
            $values = $value;
            foreach ($values as $k => $tagkeys) {
                $tagkeys = $tagkeys ? explode(",", preg_replace('/\s*,\s*/', ',', $tagkeys)) : [];
                foreach ($tagkeys as $tag) {
                    if (in_array($tag, $nameTags)) {
                        $status = '0';
                    } else {
                        $status = '1';
                    }
                    $tagkey = $this->filter->translitUrl($tag);
                    if (!$tag) {
                        continue;
                    }
                    if ($k != 0) {
                        $data[] = [
                            'tag' => $tag,
                            'tag_key' => $tagkey,
                            'store_id' => $k,
                            'product_id' => $key,
                            'status' => $status
                        ];
                    } else {
                        foreach ($storeId as $col) {
                            $data[] = [
                                'tag' => $tag,
                                'tag_key' => $tagkey,
                                'store_id' => $col,
                                'product_id' => $key,
                                'status' => $status
                            ];
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * @param $originData
     * @param $mergedData
     * @return array
     */
    private function doMergeAttrDataWithOriData($originData, $mergedData)
    {
        // Merge original data to merged data
        // Original data is data of collection, it included router_tag
        // Merged data is tags data that we got from attribute Tag keywords (See in backend product edit page)
        // We able to free type tag keywords, its may be not set router tag
        // So, it's router tag is default (catalogtags)
        if (empty($mergedData)) {
            return $originData;
        }
        foreach ($mergedData as &$mergedDatum) {
            foreach ($originData as &$originDatum) {
                if (isset($mergedDatum['tag_key']) &&
                    isset($originDatum['tag_key'])) {
                    if (!(bool)$originDatum['router_tag'] || strlen(trim($originDatum['router_tag'])) < 1) {
                        $mergedDatum['router_tag'] = $originDatum['router_tag'] = 'catalogtags';
                    } elseif ($mergedDatum['tag_key'] == $originDatum['tag_key']) {
                        $mergedDatum['router_tag'] = isset($originDatum['router_tag']) ? $originDatum['router_tag'] : 'catalogtags';
                    } elseif (!$mergedDatum['tag_key'] && strlen($mergedDatum['tag_key']) < 1) {
                        $mergedDatum['router_tag'] = 'catalogtags';
                    } else {
                        $mergedDatum['router_tag'] = 'catalogtags';
                    }
                } else {
                    continue;
                }
            }
        }
        return array_merge_recursive($originData, $mergedData);
    }

    /**
     * @return array
     */
    private function getStoreId()
    {
        $store = $this->storeManager->getStores();
        $storeId = [];
        foreach ($store as $val) {
            $storeId[] = (int)$val->getStoreId();
        }
        return $storeId;
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    private function joinTableRuleProtag()
    {
        $select = $this->getSelect()->reset()
            ->from(
                ['bss_protags_rule' => $this->getTable('bss_protags_rule')],
                ['router_tag' => 'bss_protags_rule.router_tag']
            )
            ->join(
                ['product_tag_name' => $this->getTable('bss_protags_tag')],
                'product_tag_name.protags_id = bss_protags_rule.protags_id',
                ['tag' => 'product_tag_name.name_tag']
            )
            ->join(
                ['bss_protags_rule_key' => $this->getTable('bss_protags_rule_key')],
                'bss_protags_rule_key.protags_id = bss_protags_rule.protags_id',
                ['tag_key' => 'bss_protags_rule_key.tag_key']
            )
            ->join(
                ['bss_protags_rule_store' => $this->getTable('bss_protags_rule_store')],
                'product_tag_name.protags_id = bss_protags_rule_store.protags_id',
                ['store_id' => 'bss_protags_rule_store.store_id']
            )
            ->join(
                ['bss_protags_rule_product' => $this->getTable('bss_protags_rule_product')],
                'product_tag_name.protags_id = bss_protags_rule_product.protags_id',
                ['bss_protags_rule_product.product_id']
            )
            ->where('bss_protags_rule.status = ?', 1);
        return $select;
    }

    /**
     * @return \Zend_Db_Statement_Interface|string
     * @throws \Zend_Db_Exception
     */
    private function createTemporaryTableToIndex()
    {
        $tableName = $this->getConnection()->getTableName('bss_protags_product_tagname_index_temp');
        if (!$this->getConnection()->isTableExists($tableName)) {
            $table = $this->getConnection()->newTable($tableName)
                ->addColumn(
                    'tag',
                    Table::TYPE_TEXT,
                    255,
                    ['unsigned' => true, 'nullable' => false],
                    'Name Tag'
                )
                ->addColumn(
                    'tag_key',
                    Table::TYPE_TEXT,
                    80,
                    ['unsigned' => true, 'nullable' => false],
                    'Tag Key'
                )->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false]
                )->addColumn(
                    'router_tag',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true]
                )->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Store ID'
                )->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    10,
                    ['unsigned' => true, 'nullable' => true],
                    'Status'
                );
            return $this->getConnection()->createTable($table);
        }
        if (!$this->getConnection()->tableColumnExists($tableName, 'router_tag')) {
            $this->getConnection()->addColumn(
                $tableName,
                'router_tag',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Customize Router'
                ]
            );
        }
        return $tableName;
    }
}
