<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\Review;

use Biztech\Translator\Helper\Data;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Biztech\Translator\Helper\Language;

class Add extends \Magento\Review\Block\Adminhtml\Add
{
    protected $helperData;
    protected $productMetadataInterface;
    protected $language;
    
    /**
     * @param Context                  $context
     * @param ProductMetadataInterface $productMetadataInterface
     * @param Language                 $language
     * @param Data                     $helperData
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadataInterface,
        Language $language,
        Data $helperData,
        array $data = []
    ) {
        $this->language = $language;
        $this->productMetadataInterface = $productMetadataInterface;
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->helperData->isEnabled() && $this->helperData->isTranslatorEnabled()) {
            $review_form = $this->getLayout()->createBlock('Magento\Framework\View\Element\Template')->setTemplate('Biztech_Translator::translator/minTranslate.phtml')->toHtml();
            
            $version = $this->productMetadataInterface->getVersion();
            if (version_compare($version, '2.1', '<')) {
                $requirejs = 'biztechTranslator';
            } else {
                $requirejs = 'biztechTranslatorv213';
            }
            
            $this->_formInitScripts[] = '
			require([
				"jquery",
				"'. $requirejs .'"
			], function($,biztechTranslator){
				$("#translate_button_all").on("click", function() {
					alert("bingo");
				});

				$("#edit_form").after(\'' . $review_form . '\');
				var BiztechTranslatorConfig = ' . $this->getbiztechReviewConfig() . ';
                $(document).on("click",function(){
                       var translator = biztechTranslator.BiztechTranslatorForm.init("#edit_form", BiztechTranslatorConfig);
                });
                $(window).ready(function() {
                    function translator() {
                       var translator = biztechTranslator.BiztechTranslatorForm.init("#edit_form", BiztechTranslatorConfig);
                    }
                    setTimeout(translator, 3000); 
				});
			});
			';
        }
    }

    /**
     * @return mixed
     */
    public function getbiztechReviewConfig()
    {
        $storeId = $this->getRequest()->getParam('store', 0);

        $translatedFields = 'detail';
        $url = $this->getUrl('translator/translator/translate');
        $config = $this->language->getConfiguration($url, $translatedFields, $storeId);

        return $config;
    }
}
