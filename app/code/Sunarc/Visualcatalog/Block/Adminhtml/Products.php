<?php

namespace Sunarc\Visualcatalog\Block\Adminhtml;

use Magento\Framework\App\Config\ScopeConfigInterface;
class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    private $productFactory;

    private $productRepositoryFactory;

    private $category;
    private $pageSize = 9;
    public $storeManager;
    public $priceHelper;
    public $_scopeConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        /*\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,*/
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->coreRegistry = $coreRegistry;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $context->getStoreManager();
        $this->priceHelper = $priceHelper;
       // $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _prepareLayout()
    {

        parent::_prepareLayout();
        if ($this->_getProductCollection()) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'my.custom.pager');
            $show_options = $this->getGridPerPageValue();
           // $limit = $this->getGridPerPage();
            $pager->setAvailableLimit($show_options)->setShowPerPage(true)->setCollection(
                $this->_getProductCollection()
            );
            $pager->setTemplate('Sunarc_Visualcatalog::pager.phtml');
            $this->setChild('pager', $pager);// set pager block in layout

            $pager_option = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'my.custom.pager_option');

            $pager_option->setAvailableLimit($show_options)->setShowPerPage(true)->setCollection(
                $this->_getProductCollection()
            );
            $pager_option->setTemplate('Sunarc_Visualcatalog::pager_option.phtml');
            $this->setChild('pager_option', $pager_option);// set pager block in layout

        }

        $this->getToolbar()->addChild(
            'back_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Back'),
                'onclick' => "window.location.href = '" . $this->getUrl('*/*') . "'",
                'class' => 'action-back'
            ]
        );

        $this->getToolbar()->addChild(
            'save',
            'Magento\Backend\Block\Widget\Button',
            ['id' => 'save',
                'label' => __('Save'),
                'class' => 'save primary',
                'onclick' => "submitPositionForm('" . $this->getFormAction() . "')"
            ]
        );

         $this->getToolbar()->addChild(
            'move',
            'Magento\Backend\Block\Widget\Button',
            ['id' => 'move',
                'label' => __('Move'),
                'class' => 'save primary',
                'onclick' => "submitPositionForm('" . $this->getMoveFormAction() . "')"
            ]
        );


        return $this;
    }

    /**
     * @return array|null
     */

    public function getConfig()
{
    return $this->_scopeConfig;
}

    public function getCategory()
    {
        return $this->coreRegistry->registry('category');
    }

     public function getGridPerPage()
    {
       return $this->getConfig()->getValue('catalog/frontend/grid_per_page');;
    }

     public function getGridPerPageValue()
    {
       $options = $this->getConfig()->getValue('catalog/frontend/grid_per_page_values');
       $options = explode (',' , $options);
       $show_options = array_combine($options, $options);

        return $show_options;
    }
    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }

    public function getCurrentCategory()
    {
        return $this->coreRegistry->registry('currentcategory');
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    private function _getProductCollection()
    {

        $categoryId = $this->getRequest()->getParam('id');
        $category = $this->categoryFactory->create()->load($categoryId);

        $this->_productCollection = $this->categoryFactory->create()->load($categoryId)->getProductCollection()
            ->addAttributeToSelect('*');
        $this->_productCollection->getSelect()
            ->order([new \Zend_Db_Expr("CASE WHEN `cat_index_position` = '0' THEN 9999 ELSE 1 END"), 'cat_index_position ASC']);

        //get values of current page
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : $this->getGridPerPage();

        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId > 0) {
            $this->_productCollection->addStoreFilter($storeId);
        }

        $this->_productCollection->setPageSize($pageSize);
        $this->_productCollection->setCurPage($page);

        return $this->_productCollection;
    }
   public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    public function getImageData($_product)
    {
        $product = $this->productRepositoryFactory->create()->getById($_product->getId());
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB)
            . 'pub/media/catalog/product/' . $product->getData('thumbnail');
    }

    /*
     * Get catalog price with format
     */
    public function getPriceFormat($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * Retrieve list toolbar HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getPagerOptionsHtml()
    {
        return $this->getChildHtml('pager_option');
    }

    public function getFormAction()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->getUrl('*/*/save', ['_current' => false, 'id' => $id, '_query' => false]);
    }


     public function getMoveFormAction()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->getUrl('*/*/move', ['_current' => false, 'id' => $id, '_query' => false]);
    }
}
