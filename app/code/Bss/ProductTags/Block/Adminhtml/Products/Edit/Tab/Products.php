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
namespace Bss\ProductTags\Block\Adminhtml\Products\Edit\Tab;

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Bss\ProductTags\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Bss\ProductTags\Model\Protags
     */
    protected $modelProtags;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $visibility;

    /**
     * @var string
     */
    protected $_template = 'Bss_ProductTags::widget/grid/extended.phtml';

    /**
     * Products constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Bss\ProductTags\Model\Protags $modelProtags
     * @param \Bss\ProductTags\Model\ResourceModel\Product\CollectionFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Bss\ProductTags\Model\Protags $modelProtags,
        \Bss\ProductTags\Model\ResourceModel\Product\CollectionFactory $productFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->visibility = $visibility;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->modelProtags = $modelProtags;
        $this->productFactory = $productFactory;
    }

    /**
     * _construct
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('productTags');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('protags_id')) {
            $this->setDefaultFilter(['in_product' => 1]);
        }
    }

    /**
     * @return \Bss\ProductTags\Model\Protags
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductTag()
    {
        $id = $this->getRequest()->getParam('protags_id');
        try {
            $model = $this->modelProtags->load($id);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Something went wrong while load the tag.'));
        }
        return $model;
    }

    /**
     * Add Column Filter To Collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this|\Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_product') {
            $productIds = $this->getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    public function _prepareCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('price');
        $collection->addAttributeToFilter('visibility', ['in' => [2,3,4]]);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    public function _prepareColumns()
    {
        $this->addColumn(
            'in_product',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_product',
                'align' => 'center',
                'index' => 'entity_id',
                'values' => $this->getSelectedProducts(),
                'column_css_class' => 'col-select'
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'index' => 'sku',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->visibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'index' => 'price',
                'width' => '50px',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('protag/tag/products', ['_current' => true]);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSelectedProducts()
    {
        $products = $this->getRequest()->getParam('products_related');
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedRelatedProducts());
        }
        return $products;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSelectedRelatedProducts()
    {
        $products = [];
        $productCollection = $this->productFactory->create();
        $productCollection->addFieldToFilter('protags_id', ['eq' => $this->getProductTag()->getId()]);
        foreach ($productCollection as $product) {
            $products[$product->getProductId()] = ['position' => $product->getPosition()];
        }
        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return true;
    }
}
