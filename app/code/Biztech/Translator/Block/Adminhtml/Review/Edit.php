<?php

/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */

namespace Biztech\Translator\Block\Adminhtml\Review;

use Biztech\Translator\Helper\Data;
use Biztech\Translator\Helper\Translator;
use Biztech\Translator\Helper\Language;

/**
 * Review edit form
 */
class Edit extends \Magento\Review\Block\Adminhtml\Edit
{
    protected $_jsonEncoder;
    protected $_helperData;
    protected $_reviewModel;
    protected $_translator;
    protected $_productMetadataInterface;
    protected $_language;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Helper\Action\Pager $pager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Review\Model\ReviewFactory $reviewModel,
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
        Data $helperData,
        Translator $translator,
        Language $language,
        array $data = []
    ) {
        $this->_language = $language;
        $this->_productMetadataInterface = $productMetadataInterface;
        $this->_translator = $translator;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_helperData = $helperData;
        $this->_reviewModel = $reviewModel;
        parent::__construct($context, $reviewFactory, $pager, $registry, $data);
    }

    public function _construct()
    {
        if ($this->_helperData->isEnabled() && $this->_helperData->isTranslatorEnabled()) {
            $storeId = $this->getRequest()->getParam('store', 0);
            $language = $this->_translator->getLanguage($storeId);

            $fullNameLanguage = $this->_translator->getLanguageFullNameByCode($language, $storeId);
            $translateBtnText = trim($this->_scopeConfig->getValue('translator/general/translate_btntext', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId));

            $translateBtnText = $translateBtnText ? $translateBtnText : 'Translate To ';


            $review_form = $this->getLayout()->createBlock('Magento\Framework\View\Element\Template')->setTemplate('Biztech_Translator::translator/minTranslate.phtml')->toHtml();

            $version = $this->_productMetadataInterface->getVersion();
            $lower_version = 0;
            if (version_compare($version, '2.1', '<')) {
                $this->_formInitScripts[] = '
				require([
				"jquery",
				"biztechTranslator"
				], function($,biztechTranslator){
					$("#translate_button_all").on("click", function() {
						alert("bingo");
					});

					$("#edit_form").after(\'' . $review_form . '\');
					var BiztechTranslatorConfig = ' . $this->getbiztechReviewConfig() . ';

					$(window).ready(function() {
						 function translator() {
                                                    var translator = biztechTranslator.BiztechTranslatorForm.init("edit_form", BiztechTranslatorConfig);
                                                 }
                                                setTimeout(translator, 500); 
					});
				});
				';
            } else {
                $this->_formInitScripts[] = '
				require([
				"jquery",
				"biztechTranslatorv213"
				], function($,biztechTranslator){
					$("#translate_button_all").on("click", function() {
						alert("bingo");
					});

					$("#edit_form").after(\'' . $review_form . '\');
					var BiztechTranslatorConfig = ' . $this->getbiztechReviewConfig() . ';

					$(window).ready(function() {
						 function translator() {
                                                    var translator = biztechTranslator.BiztechTranslatorForm.initReview("edit_form", BiztechTranslatorConfig);
                                                 }
                                                setTimeout(translator, 500); 
					});
				});
				';
            }
        }
        parent::_construct();
    }

    public function getbiztechReviewConfig()
    {
        $blockId = $this->getRequest()->getParam('id');
        $storeArray = $this->_reviewModel->create()->load($blockId)->getData('stores');
        if (sizeof($storeArray) == 2) {
            $storeId = $storeArray[1];
        } elseif (sizeof($storeArray) > 2) {
            $storeId = 0;
        }
        $translatedFields = 'detail';
        $url = $this->getUrl('translator/translator/translate');
        $config = $this->_language->getConfiguration($url, $translatedFields, $storeId);

        return $config;
    }
}
