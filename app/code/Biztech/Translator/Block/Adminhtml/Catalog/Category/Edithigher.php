<?php

/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */

namespace Biztech\Translator\Block\Adminhtml\Catalog\Category;

use Biztech\Translator\Helper\Data;
use Magento\Backend\Block\Widget\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\ProductMetadataInterface;
use Biztech\Translator\Helper\Language;

class Edithigher extends Field
{
    protected $_template = 'Biztech_Translator::translator/catalog/category/edit.phtml';
    protected $helperData;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadataInterface;

    /**
     * @var \Biztech\Translator\Helper\Language
     */
    protected $languagehelper;


    /**
     * @param Context $context
     * @param Language $languagehelper
     * @param ProductMetadataInterface $productMetadataInterface
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Language $languagehelper,
        ProductMetadataInterface $productMetadataInterface,
        Data $helperData,
        array $data = []
    ) {
        $this->languagehelper = $languagehelper;
        $this->productMetadataInterface = $productMetadataInterface;
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getCategoryConfiguration()
    {
        if ($this->helperData->isEnabled() && $this->helperData->isTranslatorEnabled()) {
            $storeId = $this->getRequest()->getParam('store', 0);
            $translatedFields = $this->_scopeConfig->getValue('translator/general/massaction_category_translate_fields', \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $storeId);

            $url = $this->getUrl('translator/translator/translate');

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
     * @return $this
     */
    protected function _prepareLayout()
    {

        return parent::_prepareLayout();
    }
}
