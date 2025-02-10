<?php

namespace Biztech\Translator\Block\Adminhtml\Products;

use Biztech\Translator\Helper\Data as BizHelper;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory]
     */
    protected $_setsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;
    protected $_collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;
    protected $helper;
    protected $status;
    protected $productCollection;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Biztech\Translator\Model\CrondataFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        \Magento\Framework\Module\Manager $moduleManager,
        \Biztech\Translator\Model\Config\Source\Status $status,
        BizHelper $helper,
        array $data = []
    ) {
        $this->status = $status;
        $this->_logger = $context->getLogger();
        $this->_collectionFactory = $collectionFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->moduleManager = $moduleManager;
        $this->helper = $helper;
        $this->productCollection = $productCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return string
     */
    public function getRowUrl($row)
    {
        $collection = $this->_collectionFactory->create()->load($this->getRequest()->getParam('id'));
        $storeid = $collection->getStoreId();
        if ($id = $row->getId()) {
            return $this->getUrl(
                'catalog/product/edit',
                ['id' => $id , 'store' => $storeid]
            );
        }
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }


    /**
     * @return void
     */
    protected function _construct()
    {
        if ($this->helper->isTranslatorEnabled()) {
            parent::_construct();
        }
        $this->setId('crondataGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
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

            $translationproduct = $this->productCollection->addAttributeToSelect('*')->addFieldToFilter('entity_id', ['in' => json_decode($products)])->addStoreFilter($storeid);
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

                'header' => __('Translated'),
                'index' => 'translated',
                'type' => 'options',
                'options' => $this->translateOption(),
                'renderer' => 'Biztech\Translator\Block\Adminhtml\Crondata\Renderer\TranslationStatus'
            ]
        );

        /*$this->addColumn(
            'edit_link',
            [
                'header' => __('View Product'),
                'renderer' => 'Biztech\Translator\Block\Adminhtml\Crondata\Renderer\Editlink'
            ]
        );*/

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }
    public function translateOption()
    {
         return ["" => __("All") , "1" => __('Translated') , "0" => __('Not Translated') ];
    }
}
