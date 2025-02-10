<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/

namespace Biztech\Translator\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ProductMetadataInterface;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    // protected $_template = 'Biztech_Translator::translator/catalog/product/edit.phtml';
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadataInterface;
    /**
     * @var \Biztech\Translator\Helper\Data $helperData
     */
    protected $helperData;
    /**
     * @var \Biztech\Translator\Helper\Language
     */
    protected $languagehelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface $_storeManager
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\UrlInterface $_urlInterface
     */
    protected $_urlInterface;
    /**
     * @var \Magento\Framework\App\RequestInterface $_request
     */
    protected $_request;
    /**
     * @param ScopeConfigInterface                $config
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface
     * @param \Biztech\Translator\Helper\Data $helperData
     * @param \Biztech\Translator\Helper\Language $languagehelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        ScopeConfigInterface $config,
        ProductMetadataInterface $productMetadataInterface,
        \Biztech\Translator\Helper\Data $helperData,
        \Biztech\Translator\Helper\Language $languagehelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_scopeConfig = $config;
        $this->productMetadataInterface = $productMetadataInterface;
        $this->helperData = $helperData;
        $this->languagehelper = $languagehelper;
        $this->_storeManager = $storeManager;
        $this->_urlInterface = $urlInterface;
        $this->_request = $request;
    }

    /*
     * @return mixed
     */
    public function getBiztechTranslatorConfiguration()
    {
        if ($this->helperData->isEnabled() && $this->helperData->isTranslatorEnabled()) {
            $storeId = $this->_request->getParam('store', 0);
            $translatedFields = $this->_scopeConfig->getValue('translator/general/massaction_product_translate_fields', \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $storeId);
            $url = $this->_urlInterface->getUrl('translator/translator/translate');
            $config = $this->languagehelper->getConfiguration($url, $translatedFields, $storeId);
            return $config;
        }
    }

    /**
     * Getting magneto version
     * @return String
     */
    public function getVersion()
    {
        $version = $this->productMetadataInterface->getVersion();
        return $version;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->helperData->isEnabled();
    }

    /**
     * @return StoreID
     */
    public function getStoreid()
    {
        return $this->_storeManager->getStore()->getId();
    }
    /**
     * @return ProductId
     */
    public function ProductId()
    {
        return $this->_request->getParam('id');
    }


    /**
     * getting target language to translate.
     * @param $storeid
     * @return String
     */
    public function getdefaultvalue($storeid = null)
    {
        if (is_null($storeid)) {
            return $this->_scopeConfig->getValue('translator/general/languages');
        } else {
            return $this->_scopeConfig->getValue('translator/general/languages', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeid);
        }
    }

    /**
     * Getting locale language based on the store.
     * @param  $storeId
     * @return String
     */
    public function getlocale($storeId)
    {
        return $this->_scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}
