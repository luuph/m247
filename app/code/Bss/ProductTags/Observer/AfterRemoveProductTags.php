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
namespace Bss\ProductTags\Observer;

use Magento\Framework\Event\ObserverInterface;

class AfterRemoveProductTags implements ObserverInterface
{
    /**
     * @var \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Bss\ProductTags\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $eavAttribute;

    /**
     * @var \Bss\ProductTags\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * AfterRemoveProductTags constructor.
     * @param \Bss\ProductTags\Model\ResourceModel\Product\Collection $collection
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $eavAttribute
     * @param \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Bss\ProductTags\Helper\Data $helper
     */
    public function __construct(
        \Bss\ProductTags\Model\ResourceModel\Product\Collection $collection,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttribute,
        \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory $collectionFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Bss\ProductTags\Helper\Data $helper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->helper = $helper;
        $this->eavAttribute = $eavAttribute;
        $this->collection = $collection;
    }

    /**
     * After delete product tag
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->getConfig('general/enable')) {
            $dataTag = $observer->getProductTag();
            $productsId = $observer->getProductsId();
            $attribute = $this->eavAttribute->get(\Magento\Catalog\Model\Product::ENTITY, 'product_tag');
            $attributeId = $attribute->getAttributeId();
            $tag = $dataTag['name_tag'];
            if (!is_array($dataTag['name_tag'])) {
                $tag = isset($dataTag['name_tag']) ? explode(",", $dataTag['name_tag']) : [];
            }
            foreach ($productsId as $product) {
                $data = $this->collection->getProductTags($product, $attributeId);
                $array = isset($data['value']) ? explode(",", preg_replace('/\s*,\s*/', ',', $data['value'])) : [];
                $values = array_diff($array, $tag);
                $value = implode(",", $values);
                $bind = ['value' => $value];
                $this->collection->updateProductTags($bind, $product, $attributeId);
            }
            $indexer = $this->indexerRegistry->get(\Bss\ProductTags\Model\Indexer\Protag::INDEXER_ID);
            if (!$indexer->isScheduled()) {
                $collection = $this->collectionFactory->create()
                    ->addFieldToSelect('product_id')
                    ->addFieldToFilter('tag', ['in' => $tag]);
                $productId = [];
                foreach ($collection as $value) {
                    $productId[] = $value->getProductId();
                }
                $indexer->reindexList($productId);
            }
        }
    }
}
