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
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Helper;

class HelperModelTemplate extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    protected $ruleProductFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $combineFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $iterator;

    /**\
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModelFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $metaData;

    /**
     * HelperModelTemplate constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $ruleProductFactory
     * @param \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productModelFactory
     * @param \Magento\Framework\Model\ResourceModel\Iterator $iterator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ProductMetadataInterface $metaData
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $ruleProductFactory,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productModelFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $iterator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $metaData
    ) {
        $this->ruleProductFactory= $ruleProductFactory;
        $this->combineFactory = $combineFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->iterator = $iterator;
        $this->productModelFactory = $productModelFactory;
        $this->storeManager = $storeManager;
        $this->metaData = $metaData;
        parent::__construct($context);
    }

    /**
     * @return \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    public function getRuleProductFactory()
    {
        return $this->ruleProductFactory;
    }

    /**
     * @return \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    public function getCombineFactory()
    {
        return $this->combineFactory;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public function getProductCollectionFactory()
    {
        return $this->productCollectionFactory;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Iterator
     */
    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * @return \Magento\Catalog\Model\ProductFactory
     */
    public function gettProductModelFactory()
    {
        return $this->productModelFactory;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        $column = 'entity_id';
        if ($this->metaData->getEdition() == 'Enterprise') {
            $column = 'row_id';
        }
        return $column;
    }
}
