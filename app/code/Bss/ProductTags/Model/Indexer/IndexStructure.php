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
namespace Bss\ProductTags\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Bss\ProductTags\Model\ResourceModel\ProTags\Indexer\Collection as IndexCollection;

class IndexStructure
{
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollection;

    /**
     * @var IndexCollection
     */
    protected $indexCollection;

    /**
     * @var \Bss\ProductTags\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * IndexStructure constructor.
     * @param ResourceConnection $resource
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $eavAttribute
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param \Bss\ProductTags\Helper\Data $helper
     * @param IndexCollection $indexCollection
     * @param \Magento\Catalog\Model\Product $product
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttribute,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Bss\ProductTags\Helper\Data $helper,
        IndexCollection $indexCollection,
        \Magento\Catalog\Api\ProductRepositoryInterface $product
    ) {
        $this->eavAttribute = $eavAttribute;
        $this->productCollection = $productCollection;
        $this->indexCollection = $indexCollection;
        $this->resource = $resource;
        $this->helper = $helper;
        $this->product = $product;
    }

    /**
     * @param string $where
     */
    public function deleteData($where = '')
    {
        $table = $this->resource->getTableName('bss_protags_product_tagname_index');
        $this->resource->getConnection()->delete($table, $where);
    }

    /**
     * @param array $id
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Exception
     */
    public function indexDataToProductTagTableIndex($id = [])
    {
        $useMetaKeyword = $this->helper->getConfig('general/use_meta_keyword');
        $meta = $useMetaKeyword ? $this->getAttrAllOptions('meta_keyword', $id) : [];
        $tag = $this->getAttrAllOptions('product_tag', $id);
        $this->indexCollection->setDataToTableIndex($tag, $id, $meta);
    }

    /**
     * @param string $attributeCode
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttrAllOptions($attributeCode, $id)
    {
        $data = [];
        if ($id) {
            if (is_array($id)) {
                foreach ($id as $productId) {
                    $data[$productId] = [$this->product->getById($productId)->getData($attributeCode)];
                }
            } else {
                $data[$id] = [$this->product->getById($id)->getData($attributeCode)];
            }
            return $data;
        }
        $attribute = $this->eavAttribute->get(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        return $this->productCollection->create()->getAllAttributeValues($attribute);
    }
}
