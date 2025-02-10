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
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Block\ProductTag\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Bss\ProductTags\Model\ProductFactory
     */
    protected $protagIndexFactory;

    /**
     * ListProduct constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $urlHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Bss\ProductTags\Model\ProtagIndexFactory $protagIndexFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Bss\ProductTags\Model\ProtagIndexFactory $protagIndexFactory,
        array $data = []
    ) {
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->protagIndexFactory = $protagIndexFactory;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|AbstractCollection|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $tag = $this->getRequest()->getParam('tag');
            $productTags = $this->protagIndexFactory->create()->getCollection()
                ->addFieldToSelect('product_id')
                ->addFieldToFilter('main_table.tag_key', $tag);
            $productIds = [];
            foreach ($productTags as $productTag) {
                $productIds[] = $productTag->getProductId();
            }

            $this->_productCollection = $this->productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', $productIds)
                ->addAttributeToFilter('visibility', ['in' => [2,3,4]])
                ->load();
        }
        return $this->_productCollection;
    }
}
