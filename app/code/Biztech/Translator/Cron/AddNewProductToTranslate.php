<?php

namespace Biztech\Translator\Cron;

use Biztech\Translator\Helper\NewAddedProductTranslate\Logger;
use Biztech\Translator\Helper\Data as BizHelper;
use Biztech\Translator\Model\MasstranslateNewlyAddedProductsFactory;
use Biztech\Translator\Model\Translator;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Biztech\Translator\Helper\Translator as TranslatorHelper;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AddNewProductToTranslate
{
    protected $_bizHelper;
    protected $_date;
    protected $_masstranslateNewlyAddedProductsFactory;
    protected $_productModelFactory;
    protected $_translatorModel;
    protected $_logger;
    protected $timezone;
    protected $_translatorHelper;
        protected $resourceConfig;
         protected $scopeConfig;
         protected $_cacheTypeList;
    protected $_cacheFrontendPool;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param BizHelper                                   $bizHelper
     * @param MasstranslateNewlyAddedProductsFactory      $masstranslateNewlyAddedProductsFactory
     * @param ProductFactory                              $productFactory
     * @param Translator                                  $translatorModel
     * @param Logger                                      $logger
     * @param TimezoneInterface                           $timezone
     * @param TranslatorHelper           $translatorHelper
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        BizHelper $bizHelper,
        MasstranslateNewlyAddedProductsFactory $masstranslateNewlyAddedProductsFactory,
        ProductFactory $productFactory,
        Translator $translatorModel,
        Logger $logger,
        TimezoneInterface $timezone,
        TranslatorHelper $translatorHelper,
        Config $resourceConfig,
        ScopeConfigInterface $scopeConfig,
         \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->_translatorModel = $translatorModel;
        $this->_date = $datetime;
        $this->_bizHelper = $bizHelper;
        $this->_masstranslateNewlyAddedProductsFactory = $masstranslateNewlyAddedProductsFactory;
        $this->_productModelFactory = $productFactory;
        $this->_logger = $logger;
        $this->timezone = $timezone;
         $this->resourceConfig = $resourceConfig;
        $this->_translatorHelper = $translatorHelper;
        $this->scopeConfig = $scopeConfig;
         $this->_cacheTypeList = $cacheTypeList;
          $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * @return CheckBizTranslateCron
     */
    public function execute()
    {

        if ($this->_bizHelper->isTranslatorEnabled() && $this->_bizHelper->newAddedProductTranslateEnable()) {
            $_cronModel = $this->_masstranslateNewlyAddedProductsFactory->create();
            $_cronProducts = $_cronModel->getCollection()->addFieldToFilter('status', ['eq' => 'pending']);
            if ($_cronProducts->count()) {
                $this->_logger->info("==========================================");
                $this->_logger->info("Seems one cron already runnig to Translate");
                $this->_logger->info("==========================================");
                return;
            }

            $storeData = $this->_bizHelper->newAddedProductTranslateEnabledStores();
            $store_ids = array_keys($storeData);
            $oldAddedProductTranslateEnable = $this->_bizHelper->oldAddedProductTranslateEnable();
            $pModel = $this->_productModelFactory->create();
            $lastId=null;
            if ($oldAddedProductTranslateEnable) {

                $productFrom = $this->_bizHelper->moduleInstallDate();

                $lastId = $this->scopeConfig->getValue("translator/general/old_product_last_id");
                if($lastId ==null){
                    $lastId = 1;
                 }
                // $productModel = $pModel->getCollection()->addAttributeToSort('entity_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC)->addFieldToFilter('entity_id' , ['gt' => $lastId])->addFieldToSelect('entity_id')->setPageSize(15);
                $productModel = $pModel->getCollection()->addAttributeToSort('entity_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC)->addFieldToFilter('entity_id' , ['lt' => $lastId])->addFieldToSelect('entity_id')->setPageSize(15);
            } else {
                $productFrom = $this->_bizHelper->newAddedProductDate();
                 $lastId = $this->scopeConfig->getValue("translator/general/newly_product_last_id");
                // $productModel = $pModel->getCollection()->addAttributeToSort('entity_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC)->addFieldToFlter('created_at', ['gt' => $productFrom])->addFieldToFilter('entity_id' , ['gt' => $lastId])->setPageSize(1000);

                $productModel = $pModel->getCollection()
                ->addAttributeToSort('entity_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
                ->addFieldToFilter('created_at', ['gt' => $productFrom])
                ->addFieldToFilter('entity_id', ['gt' => $lastId])
                ->setPageSize(1000);
            }
            $products = [];
            $translateAll = $this->_bizHelper->getConfigValue('translator/general/translate_all');
            foreach ($productModel as $product_id => $product) {
                foreach ($store_ids as $key => $store_id) {
                    $productModelnew = $this->_productModelFactory->create()->setStoreId($store_id)->load($product->getEntityId());
                    if ($productModelnew->getTranslated()==0 || ($productModelnew->getTranslated()==1 && $translateAll==1)) {
                        $products[$product_id] = $product->getEntityId();
                    }
                }
            }
            $productids = [];
            foreach(array_chunk($products ,10) as $products){
                $productids = $products;
                break;
            }
            if(!empty($productids))
                 sort($productids);
            if ($oldAddedProductTranslateEnable) {
                            if(empty($productids)){
                                $lastproducts = $lastId + 15;
                            }else{
                                $lastproducts = reset($productids);
                            }
                    $this->resourceConfig->saveConfig('translator/general/old_product_last_id', $lastproducts, 'default', 0);
            }
            else
            {
                $this->resourceConfig->saveConfig('translator/general/newly_added_product_date', $this->_date->gmtDate(), 'default', 0);
                 $this->resourceConfig->saveConfig('translator/general/newly_product_last_id', end($productids), 'default', 0);
            }
                     $types = ['config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice'];
                            foreach ($types as $type) {
                                $this->_cacheTypeList->cleanType($type);
                            }
                            foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                                $cacheFrontend->getBackend()->clean();
                            }
            $to_lang = [];
            $from_lang = [];
            if (!count($products)) {
                $this->_logger->info("===============================================");
                $this->_logger->info("Seems all new added products are translated");
                $this->_logger->info("===============================================");
                return;
            }
            foreach ($store_ids as $key => $store_Id) {
                /*Language To*/
                $to_lang[$store_Id] = $this->_translatorHelper->getLanguage($store_Id);
                /*Language From*/
                $from_lang[$store_Id] = $this->_translatorHelper->getFromLanguage($store_Id);
            }
            try {
                $cronTranslate = $this->_masstranslateNewlyAddedProductsFactory->create();
                $cronTranslate->setCronName('Cron new product Translation')
                    ->setStoreIds(json_encode($store_ids))
                    ->setProductIds(json_encode($productids))
                    ->setStatus('pending')
                    ->setLangFrom(json_encode($from_lang))
                    ->setLangTo(json_encode($to_lang))
                    ->setCronDate($this->_date->gmtDate())
                    ->setUpdateCronDate($this->_date->gmtDate());
                $cronTranslate->save();
                $jobCode = $cronTranslate::NEWLY_ADDED_PRODUCT_TRANSLATE_CRON_JOB_CODE;
                $cronSet = $this->_translatorModel->setTranslateCron(0, $jobCode);
                $this->_logger->info("===============================================");
                $this->_logger->info(json_encode($products)." Products added to Translation");
                $this->_logger->info("===============================================");
            } catch (\LocalizedException $e) {
                $this->_logger->debug($e->getRawMessage());
            }
        }
    }
}