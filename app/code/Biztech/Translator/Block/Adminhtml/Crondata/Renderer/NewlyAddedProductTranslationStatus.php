<?php

namespace Biztech\Translator\Block\Adminhtml\Crondata\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Context;
use Biztech\Translator\Model\MasstranslateNewlyAddedProductsFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductFactory;

class NewlyAddedProductTranslationStatus extends AbstractRenderer
{
    protected $_storeManager;
    protected $_collectionFactory;
    protected $_productCollectionFactory;

    public function __construct(
        Context $context,
        MasstranslateNewlyAddedProductsFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        ProductFactory $productCollectionFactory
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }

    public function render(DataObject $row)
    {
        $id = $this->getRequest()->getParam('id');
        $collection = $this->_collectionFactory->create()->load($id);
        $stores = json_decode($collection->getStoreIds());
        $store_translate= '';
        foreach ($stores as $key => $store_id) {
            $productModel = $this->_productCollectionFactory->create()->setStoreId($store_id)->load($row->getEntityId());
            $storeName = $this->_storeManager->getStore($store_id)->getName();
            if ($productModel->getTranslated() == 1) {
                $store_translate.= "<span class='grid-severity-notice'><span>$storeName → Translated </span></span>"."<br>";
            } else {
                $store_translate.= "<span class='grid-severity-critical'><span>$storeName → Not Translated </span></span>"."<br>";
            }
        }
        return $store_translate;
    }
}
