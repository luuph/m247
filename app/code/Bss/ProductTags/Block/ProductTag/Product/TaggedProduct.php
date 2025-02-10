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
namespace Bss\ProductTags\Block\ProductTag\Product;

use function Aws\filter;

/**
 * Class TaggedProduct
 * @package Bss\ProductTags\Block\ProductTag\Product
 */
class TaggedProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * Product Tags Enable
     */
    const TAG_ENABLE = 1;
    /**
     * @var \Magento\Catalog\Block\Product\View
     */
    protected $product;
    /**
     * @var \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \Bss\ProductTags\Helper\Data
     */
    protected $helper;
    /**
     * @var \Bss\ProductTags\Model\ResourceModel\ProTags\CollectionFactory
     */
    protected $protagsCollection;

    /**
     * TaggedProduct constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Block\Product\View $product
     * @param \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory $collectionFactory
     * @param \Bss\ProductTags\Helper\Data $helper
     * @param \Bss\ProductTags\Model\ResourceModel\ProTags\CollectionFactory $protagsCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Block\Product\View $product,
        \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory $collectionFactory,
        \Bss\ProductTags\Helper\Data $helper,
        \Bss\ProductTags\Model\ResourceModel\ProTags\CollectionFactory $protagsCollection,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->product = $product;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->protagsCollection = $protagsCollection;
    }

    /**
     * Get tags by product id
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTagbyProductId()
    {
        if ($this->helper->getConfig('general/enable')) {
            if ($this->helper->getConfig('general/tag_in_productpage')) {
                $order = $this->helper->getConfig('general/tag_sort_by');
                $storeId = $this->_storeManager->getStore(true)->getId();
                $product = $this->product->getProduct();
                if ($product) {
                    $productId = $product->getId();
                    $collection = $this->collectionFactory->create()
                        ->addFieldToFilter('product_id', $productId)
                        ->addFieldToFilter('store_id', ['in' => [0, $storeId]])
                        ->addFieldToFilter('status', ['in' => self::TAG_ENABLE]);
                    $data = [];

                    if ($order == 'ASC') {
                        $collection->setOrder('tag', 'asc');
                        foreach ($collection as $col) {
                            $data[] = [
                                'tag_name' => $col->getTag(),
                                'tag_key' => $col->getTagKey(),
                                'router_tag' => $col->getRouterTag()
                            ];
                        }
                    } elseif ($order == 'DESC') {
                        $collection->setOrder('tag', 'DESC');
                        foreach ($collection as $col) {
                            $data[] = [
                                'tag_name' => $col->getTag(),
                                'tag_key' => $col->getTagKey(),
                                'router_tag' => $col->getRouterTag()
                            ];
                        }
                    } else {
                        return $this->setOrderByNumberProduct($collection, $storeId);
                    }
                    return $data;
                }
            }
        }
        return [];
    }

    /**
     * @param array $collection
     * @param string $storeId
     * @return array
     */
    private function setOrderByNumberProduct($collection, $storeId)
    {
        $tag = [];
        foreach ($collection as $value) {
            $tag[$value->getTag()] = [
                'tag_name' => $value->getTag(),
                'tag_key' => $value->getTagKey(),
                'router_tag' => $value->getRouterTag()
            ];
        }
        if (empty($tag)) {
            return [];
        }
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('store_id', ['in' => [0, $storeId]])
            ->addFieldToFilter('tag', ['in' => $tag]);
        $collection->getSelect()
            ->columns('COUNT(*) as count')
            ->group('tag')
            ->order('count DESC');
        $dataTag = [];
        foreach ($collection as $value) {
            if (isset($tag[$value->getTag()])) {
                $dataTag[] = $tag[$value->getTag()];
            }
        }
        return $dataTag;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTitleListTag()
    {
        return $this->helper->getConfig('general/title');
    }
}
