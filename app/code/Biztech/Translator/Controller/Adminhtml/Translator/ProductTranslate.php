<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Controller\Adminhtml\Translator;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Biztech\Translator\Helper\Language;
use Biztech\Translator\Helper\Logger\Logger;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action as ProductAction;

class ProductTranslate extends Action
{
    protected $scopeConfig;
    protected $langHelper;
    protected $translatorHelper;
    protected $translatorModel;
    protected $logger;
    protected $product;
    protected $_productAction;

    /**
     * @param Context                               $context
     * @param ScopeConfigInterface                  $scopeConfig
     * @param Logger                                $logger
     * @param Product                               $product
     * @param Language                              $langHelper
     * @param \Biztech\Translator\Helper\Translator $translatorHelper
     * @param \Biztech\Translator\Model\Translator  $translatorModel
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        Product $product,
        Language $langHelper,
        \Biztech\Translator\Helper\Translator $translatorHelper,
        \Biztech\Translator\Model\Translator $translatorModel,
        ProductAction $_productAction,
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->langHelper = $langHelper;
        $this->translatorHelper = $translatorHelper;
        $this->product = $product;
        $this->translatorModel = $translatorModel;
        $this->_productAction = $_productAction;

        parent::__construct($context);
    }

    /**
     * Translating product based on the store.
     * @return json response.
     */
    public function execute()
    {

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        
        $id = $this->getRequest()->getParam('product_id');
        $storeId = $this->getRequest()->getParam('store_id');

        if ($storeId == null) {
            $storeId = 0;
        }
        $translatedProductCount = 0;
        $languages =  $this->langHelper->getLanguages();
        if ($this->getRequest()->getParam('lang_to') != 'locale') {
            $langTo = $this->getRequest()->getParam('lang_to');
        } else {
            $langTo = $this->translatorHelper->getLanguage($storeId);
        }
        $langFrom = $this->translatorHelper->getFromLanguage($storeId);

        try {
            $product = $this->product->setStoreId($storeId)->load($id);
            $productTranslateFields = $this->scopeConfig->getValue('translator/general/massaction_product_translate_fields', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            if (!$product || !$productTranslateFields) {
            }
            $finalTranslateFields = array_values(explode(',', !$productTranslateFields==null?$productTranslateFields:''));

            foreach ($finalTranslateFields as $translateField) {
                if (!isset($product[$translateField]) || empty($product[$translateField])) {
                    continue;
                }
                /* skip translation for specific word*/
                $skipped_word = preg_split('/\r?\n/', $this->scopeConfig->getValue('translator/general/product_skip_translation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId) ?? '');
                $m = array();
                $n = array();
                $skip_Hash = preg_match_all('/#([^#]+)#/', $product[$translateField], $m);
                $skip_counce = preg_match_all('/{{([^}}]+)}}/', $product[$translateField], $n);
                if ($skip_counce > 0) {
                    foreach ($n[0] as $data) {
                        array_push($skipped_word, $data);
                    }
                }
                if ($skip_Hash > 0) {
                    foreach ($m[0] as $data) {
                        array_push($skipped_word, $data);
                    }
                }
                
                foreach ($skipped_word as $key => $value) {
                    $product[$translateField] = str_replace($value, '<span translate=\'no\'>' . "$value" . '</span>', $product[$translateField]);
                }
                /*end*/
                $translate = $this->translatorModel->getTranslate($product[$translateField], $langTo, $langFrom);
                /* skip translation for specific word*/
                $translate['text'] = str_replace('<span translate=\'no\'>', '', $translate['text']);
                $translate['text'] = str_replace('</span>', '', $translate['text']);
                /*end*/
                if ($translate['status'] == 'fail') {
                    $this->logger->error('"' . $product->getName() . '" can\'t be translate for "Product attribute :' . $translateField . '". Error: ' . $translate['text']);
                    $this->messageManager->addError('"' . $product->getName() . '" can\'t be translate for "Product attribute :' . $translateField . '". Error: ' . $translate['text']);
                    continue;
                } else {
                    //$product->setData($translateField, $translate['text']);
                    $this->_productAction->updateAttributes(
                                        [$product->getId()],
                                        [ $translateField => $translate['text']],
                                        $storeId
                                    );
                }
            }
            try {
                //$product->save();
                if (isset($translate['status']) && $translate['status'] != 'fail') {
                    $translatedProductCount++;
                }
            } catch (LocalizedException $e) {
                $this->logger->debug($e->getRawMessage());
            }

            if ($translatedProductCount == 0) {
                $this->messageManager->addError(__('No Product has been translated. Detail info see in log'));
            } else {
                $langTo = $languages[$langTo];
                $this->messageManager->addSuccess($translatedProductCount . __(' product was translated to ') . $langTo);
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getRawMessage());
            return;
        }
        $result = 1;
        $this->getResponse()->setBody($result);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
