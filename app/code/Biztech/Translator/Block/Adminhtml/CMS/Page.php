<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\CMS;

use Biztech\Translator\Helper\Data;
use Magento\Backend\Block\Widget\Context;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\ProductMetadataInterface;
use Biztech\Translator\Helper\Language;

class Page extends \Magento\Cms\Block\Adminhtml\Page
{
    protected $helperData;
    protected $cmspageModel;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadataInterface;

    /**
     * @var \Biztech\Translator\Helper\Language
     */
    protected $languagehelper;

    /**
     * @param Context                  $context
     * @param Data                     $helperData
     * @param PageFactory              $cmspageModel
     * @param Language                 $languagehelper
     * @param ProductMetadataInterface $productMetadataInterface
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        PageFactory $cmspageModel,
        Language $languagehelper,
        ProductMetadataInterface $productMetadataInterface,
        array $data = []
    ) {
        $this->languagehelper = $languagehelper;
        $this->productMetadataInterface = $productMetadataInterface;
        $this->helperData = $helperData;
        $this->cmspageModel = $cmspageModel;
        parent::__construct($context, $data);
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
     * @return mixed
     */
    public function getCMSConfig()
    {

        $pageId = $this->getRequest()->getParam('page_id');
        $page = $this->cmspageModel->create()->load($pageId)->getData();
        if (!empty($page)) {
            $pageStoreIds = $page['store_id'];
            foreach ($pageStoreIds as $key => $value) {
                $storeId = $value;
            }
            if (sizeof($pageStoreIds) > 1) {
                $storeId = 1;
            }
        } else {
            $storeId = 0;
        }
        $translatedFields = $this->_scopeConfig->getValue('translator/general/massaction_cmspage_translate_fields', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $url = $this->getUrl('translator/translator/translateCMS');
        $config = $this->languagehelper->getConfiguration($url, $translatedFields, $storeId);
        return $config;
    }
}
