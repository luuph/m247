<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\CMS;

use Biztech\Translator\Helper\Data;
use Magento\Backend\Block\Widget\Context;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\ProductMetadataInterface;
use Biztech\Translator\Helper\Language;

class Block extends \Magento\Framework\View\Element\Template
{
    protected $helperData;
    protected $cmsBlockModel;

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
     * @param BlockFactory             $cmsBlockModel
     * @param Language                 $languagehelper
     * @param ProductMetadataInterface $productMetadataInterface
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        BlockFactory $cmsBlockModel,
        Language $languagehelper,
        ProductMetadataInterface $productMetadataInterface,
        array $data = []
    ) {
        $this->languagehelper = $languagehelper;
        $this->productMetadataInterface = $productMetadataInterface;
        $this->helperData = $helperData;
        $this->cmsBlockModel = $cmsBlockModel;
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
        $blockId = $this->getRequest()->getParam('block_id');
        $block = $this->cmsBlockModel->create()->load($blockId)->getData();
        if (!empty($block)) {
            $blockStoreId = $block['store_id'];
            foreach ($blockStoreId as $key => $value) {
                $storeId = $value;
            }
            if (sizeof($blockStoreId) > 1) {
                $storeId = 1;
            }
        } else {
            $storeId = 0;
        }
        $translatedFields = 'title,content';
        $url = $this->getUrl('translator/translator/translateCMS');
        $config = $this->languagehelper->getConfiguration($url, $translatedFields, $storeId);
        return $config;
    }
}
