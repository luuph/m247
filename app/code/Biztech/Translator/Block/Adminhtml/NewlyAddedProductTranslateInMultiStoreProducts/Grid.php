<?php

namespace Biztech\Translator\Block\Adminhtml\NewlyAddedProductTranslateInMultiStoreProducts;

use Biztech\Translator\Helper\Data as BizHelper;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_collectionFactory;
    protected $helper;
    protected $productCollection;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Biztech\Translator\Model\MasstranslateNewlyAddedProductsFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        BizHelper $helper,
        array $data = []
    ) {
        $this->_logger = $context->getLogger();
        $this->_collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->productCollection = $productCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return string
     */
    public function getRowUrl($row)
    {
        $id = $this->getRequest()->getParam('id');
        $collection = $this->_collectionFactory->create()->load($id);
        if ($id = $row->getId()) {
            return $this->getUrl(
                'catalog/product/edit',
                ['id' => $id]
            );
        }
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/Newlyaddedproductgrid', ['_current' => true]);
    }


    /**
     * @return void
     */
    protected function _construct()
    {
        if ($this->helper->isTranslatorEnabled()) {
            parent::_construct();
        }
        $this->setId('NewlyaddedproducttranslateinmultiplestoreGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        try {
            $collection = $this->_collectionFactory->create()->load($this->getRequest()->getParam('id'));
            $products = $collection->getProductIds();
            $storeid = $collection->getStoreId();

            $translationproduct = $this->productCollection->addAttributeToSelect('*')->addFieldToFilter('entity_id', ['in' => json_decode($products)]);
            $this->setCollection($translationproduct);
            parent::_prepareCollection();
            return $this;
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
        }
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name'
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku'
            ]
        );

        $this->addColumn(
            'translated',
            [
                'header' => __('Store translation status'),
                'index' => 'translated',
                 'filter' => false,
                'sortable' => false,
                'renderer' => 'Biztech\Translator\Block\Adminhtml\Crondata\Renderer\NewlyAddedProductTranslationStatus'
            ]
        );
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }
}
