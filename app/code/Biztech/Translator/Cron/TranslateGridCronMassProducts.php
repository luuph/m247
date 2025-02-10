<?php

namespace Biztech\Translator\Cron;

use Biztech\Translator\Helper\Cron\Logger;
use Biztech\Translator\Helper\Data as BizHelper;
use Biztech\Translator\Helper\Translator;
use Biztech\Translator\Model\Crondata;
use Biztech\Translator\Model\Logcron;
use Biztech\Translator\Model\Translator as TranslatorModel;
use Magento\CatalogUrlRewrite\Block\UrlKeyRenderer;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Url;
use Magento\Catalog\Model\ResourceModel\Product\Action as ProductAction;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;

/**
 * @property mixed _logger
 * @property mixed _languageHelper
 * @property mixed _date
 * @property mixed _productModelFactory
 */
class TranslateGridCronMassProducts
{
    protected $_date;
    protected $urlPersist;
    protected $_logger;
    protected $_bizHelper;
    protected $_languageHelper;
    protected $_productModelFactory;
    protected $_cronData;
    protected $_logCron;
    protected $_translatorModel;
    protected $_url;
    protected $_productAction;
    protected $_productUrlRewrite;
    protected $_scopeConfig;
    /**
     * @param ProductFactory             $productFactory
     * @param Translator                 $languageHelper
     * @param BizHelper                  $bizHelper
     * @param Logger                     $logger
     * @param Logcron                    $_logCron
     * @param Url                        $_url
     * @param ProductUrlRewriteGenerator $_productUrlRewrite
     * @param ProductAction              $_productAction
     * @param Crondata                   $_cronData
     * @param TranslatorModel            $_translatorModel
     * @param UrlPersistInterface        $_urlPersist
     * @param DateTime                   $_dateTime
     */
    public function __construct(ProductFactory $productFactory, Translator $languageHelper, BizHelper $bizHelper, Logger $logger, Logcron $_logCron, Url $_url, ProductUrlRewriteGenerator $_productUrlRewrite, ProductAction $_productAction, Crondata $_cronData, TranslatorModel $_translatorModel, UrlPersistInterface $_urlPersist, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, DateTime $_dateTime)
    {
        $this->_date = $_dateTime;
        $this->_cronData = $_cronData;
        $this->_logCron = $_logCron;
        $this->_url = $_url;
        $this->_productUrlRewrite = $_productUrlRewrite;
        $this->_productAction = $_productAction;
        $this->_translatorModel = $_translatorModel;
        $this->urlPersist = $_urlPersist;
        $this->_logger = $logger;
        $this->_bizHelper = $bizHelper;
        $this->_languageHelper = $languageHelper;
        $this->_productModelFactory = $productFactory;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     *
     */
    public function execute()
    {

        $jobCode = \Biztech\Translator\Model\Crondata::BIZ_CRON_JOB_CODE;
        try
        {
            if (!$this
                ->_bizHelper
                ->isTranslatorEnabled())
            {
                throw new \Exception(__("Language Translator extension is not enabled. Please enable it from Stores → Configuration → AppJetty  → Translator → Translator Activation."));
                $this->_errors[] = __("Language Translator extension is not enabled. Please enable it from Stores → Configuration → AppJetty  → Translator → Translator Activation.");
                return $this;
            }

            $_logCron = $this
                ->_logCron
                ->getCollection()
                ->getLastItem();
            $_charCutLimit = (int)$this
                ->_bizHelper
                ->getConfigValue('translator/general/google_daily_cut_before_limit');
            if (!empty($_logCron->getData()))
            {
                /* Remaining limit */
                if ($this
                    ->_date
                    ->gmtDate('d-m-Y') == date('d-m-Y', strtotime($_logCron->getCronDate())) && $_logCron->getRemainLimit() <= 0)
                {
                    $this
                        ->_logger
                        ->info('  Daily Limit Reached! For the Day ' . date('d-m-Y H:i:s', time()));
                    throw new \Exception(__("Daily Limit Reached! Please try again later!"));
                    return;
                }
            }

            $this
                ->_logger
                ->info('=======================================================');
            $this
                ->_logger
                ->info('  Start Translation ' . date('d-m-Y H:i:s', time()));

            $batchSize = $this
                ->_bizHelper
                ->getConfigValue('translator/general/product_batch_size') ? (int)$this
                ->_bizHelper
                ->getConfigValue('translator/general/product_batch_size') : 20;

            $_cronProducts = $this
                ->_cronData
                ->getCollection()
                ->addFieldToFilter('status', 'pending')
                ->addFieldToFilter('is_console', '0');
            $characterLimit = (int)$this
                ->_bizHelper
                ->getConfigValue('translator/general/google_daily_limit') - $_charCutLimit;
            foreach ($_cronProducts as $cronProductData)
            {
                $_logCron = $this
                    ->_logCron
                    ->getCollection()
                    ->getLastItem();

                if (!empty($_logCron->getData()))
                {
                    if ($this
                        ->_date
                        ->gmtDate('d-m-Y') == date('d-m-Y', strtotime($_logCron->getCronDate())) && $_logCron->getRemainLimit() <= 0)
                    {
                        /*  Daily Limit Exceed Error. */
                        $this
                            ->_logger
                            ->info('  Daily Limit Reached! For the Day ' . date('d-m-Y H:i:s', time()));
                        throw new \Exception("Daily Limit Reached! Please try again later!", 1);
                        return;
                    }
                    /* Remaining limit */
                    if ($this
                        ->_date
                        ->gmtDate('d-m-Y') == date('d-m-Y', strtotime($_logCron->getCronDate())) && $_logCron->getRemainLimit() > 0)
                    {
                        $characterLimit = $_logCron->getRemainLimit();
                    }
                }

                /*Language To*/
                if ($cronProductData->getLangTo() == '')
                {
                    $langTo = $this
                        ->_languageHelper
                        ->getLanguage($cronProductData->getStoreId());
                }
                else
                {
                    $langTo = $cronProductData->getLangTo();
                }

                /*Language From*/
                if ($cronProductData->getLangFrom() == '')
                {
                    $langFrom = $this
                        ->_languageHelper
                        ->getFromLanguage($cronProductData->getStoreId());
                }
                else
                {
                    $langFrom = $cronProductData->getLangFrom();
                }
                $_productIds = json_decode($cronProductData->getProductIds());
                $bachData = [];

                    $lastTranslatedProductId = $cronProductData->getLastProductId();
                        $updateProductsIds = array_filter($_productIds , function($value) use ($lastTranslatedProductId) {
                            return $value >= $lastTranslatedProductId;
                        });
                foreach (array_chunk($updateProductsIds, $batchSize) as $productId)
                {
                    if ($cronProductData->getIsAbort() == 0)
                    {
                        $c = $this
                            ->_cronData
                            ->load($cronProductData->getId());
                        if ($c->getIsAbort() == 1)
                        {
                            break;
                        }
                    }
                    if ($characterLimit > 0)
                    {
                        $batchCount = count($productId);
                        $this
                            ->_logger
                            ->debug("     Store {$cronProductData->getStoreId() } Batch Product Count {$batchCount}");
                        $bachData = $this->batchproductTranslate($cronProductData->getStoreId() , $langTo, $langFrom, $productId, $characterLimit, $jobCode, $cronProductData->getId());
                    }
                    else
                    {
                        $_logCron = $this
                            ->_logCron
                            ->getCollection()
                            ->getLastItem();

                        if ($this
                            ->_date
                            ->gmtDate('d-m-Y') == date('d-m-Y', strtotime($_logCron->getCronDate())))
                        {
                            break;
                        }
                        else
                        {
                            $this
                                ->_logCron
                                ->setCronJobCode($jobCode)->setStatus($status)->setStoreId($storeId)->setRemainLimit($characterLimit)->setProductId($_lastSuccessProductId)->save();
                        }
                        return;
                    }
                    /* END Added: 10-11-2016 BC:Ashish Task : Remaining limit issue */
                }
                $_logCron = $this
                    ->_logCron
                    ->getCollection()
                    ->getLastItem();
                $cronLastId = end($_productIds);
                if (isset($bachData["failProducts"]) && !empty($bachData["failProducts"]) && ($cronLastId != $lastTranslatedProductId))
                {
                    $_cronupdate = $this
                        ->_cronData
                        ->load($cronProductData->getId());

                    $_cronupdate->setStatus('abort1')
                        ->setUpdateCronDate($this
                        ->_date
                        ->gmtDate())
                        ->save();
                }
                else
                {

                    $_cronupdate = $this
                        ->_cronData
                        ->load($cronProductData->getId());
                    if ($_cronupdate->getIsAbort() == 1)
                    {
                        $_cronupdate->setStatus('abort1')
                            ->setUpdateCronDate($this
                            ->_date
                            ->gmtDate())
                            ->save();
                    }
                    else
                    {
                        $_cronupdate->setStatus('success')
                            ->setUpdateCronDate($this
                            ->_date
                            ->gmtDate())
                            ->save();
                    }
                }

                $this
                    ->_logger
                    ->info('      End Translation for Store ' . $cronProductData->getStoreId());
                unset($bachData["failProducts"]);
            }

            $this
                ->_logger
                ->info(' End Translation ' . date('d-m-Y H:i:s', time()));
        }
        catch(\Exception $e)
        {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/Translateerrror.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info(print_r($e->getMessage() , true));
        }
    }

    /**
     * @param $storeId
     * @param $langTo
     * @param $langFrom
     * @param $batchProducts
     * @param $characterLimit
     * @param $jobCode
     * @param null $cronId
     */
    protected function batchproductTranslate($storeId, $langTo, $langFrom, $batchProducts, &$characterLimit, $jobCode, $cronId = null)
    {
        $_lastSuccessProductId = 0;
        $_failCount = 0;
        $_skipCount = 0;
        $_successCount = 0;
        $remainChar = 0;
        $i = 0;
        $failProducts["failProducts"] = [];

        foreach ($batchProducts as $batchProduct)
        {
            $i++;

            if (isset($batchProduct['entity_id']))
            {
                $productId = $batchProduct['entity_id'];
            }
            else
            {
                $productId = $batchProduct;
            }

            if ($i == 10)
            {
                if ($cronId)
                {
                    $_checkCron = $this
                        ->_cronData
                        ->load($cronId);
                    if ($_checkCron->getIsAbort() == 1)
                    {
                        $this->logCronEntries($jobCode, $storeId, $characterLimit,$_checkCron,$productId, 1);
                        return $this;
                    }
                }
            }

            $this
                ->_logger
                ->debug("             Translation For Batch Product {$productId} to {$langTo}");
            $this
                ->_logger
                ->debug("                 : characterlimit {$characterLimit}");

            $productModel = $this
                ->_productModelFactory
                ->create();
            $product = $productModel->setStoreId($storeId)->load($productId);
            $attributes = $this
                ->_scopeConfig
                ->getValue('translator/general/massaction_product_translate_fields', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            $translateAll = $this
                ->_bizHelper
                ->getConfigValue('translator/general/translate_all');
            $finalAttributeSet = array_values(explode(',', $attributes));
            if (($translateAll == 1 && $product->getTranslated() == 1) || ($translateAll == 1 && $product->getTranslated() == 0) || ($translateAll == 0 && $product->getTranslated() == 0))
            {
                $charCount = 0;
                foreach ($finalAttributeSet as $attributeCode)
                {
                    if (!isset($product[$attributeCode]) || empty($product[$attributeCode]))
                    {
                        continue;
                    }
                    else
                    {
                        $charCount += mb_strlen($product[$attributeCode]);
                    }
                }
                $remainChar = $characterLimit - $charCount;

                if ($remainChar > 0)
                {
                    $_lastSuccessProductId = $productId;
                    foreach ($finalAttributeSet as $attributeCode)
                    {
                        if (!isset($product[$attributeCode]) || empty($product[$attributeCode]))
                        {
                            continue;
                        }
                        try
                        {   
                            /* compitible with page builder start here */
                            if($attributeCode == 'url_key')
                            {
                                $product[$attributeCode] = str_replace('-', ' ', $product
                                [$attributeCode]);
                            }
                            if ($attributeCode == "description")
                            {
                                $m = array();
                                $n = array();

                                $skip_counce = preg_match_all('/{{([^}}]+)}}/', $product[$attributeCode], $n);
                                if ($skip_counce > 0)
                                {
                                    foreach ($n[0] as $Skip_data => $value)
                                    {
                                        if (!str_contains($value, '{{store'))
                                        {
                                            $product[$attributeCode] = str_replace($value, '<span translate=\'no\'>' . "$value" . '</span>', $product[$attributeCode]);
                                        }
                                    }
                                }

                                $product[$attributeCode] = str_replace('&lt;', '<span translate=\'no\'>&lt;', $product[$attributeCode]);
                                $product[$attributeCode] = str_replace('&gt;', '&gt;</span>', $product[$attributeCode]);

                            }

                            /* compitible with page builder end here */
                            $translate = $this
                                ->_translatorModel
                                ->getTranslate($product[$attributeCode], $langTo, $langFrom);
                            /* compitible with page builder start here */
                            if ($attributeCode == "description")
                            {
                                $m = array();
                                $n = array();
                                $skip_counce = preg_match_all('/{{([^}}]+)}}/', $translate['text'], $n);

                                if ($skip_counce > 0)
                                {
                                    foreach ($n[0] as $Skip_data => $value)
                                    {
                                        $translateHtml = \html_entity_decode($value);
                                        $translate["text"] = str_replace($value, $translateHtml, $translate['text']);
                                    }
                                }
                                $translate['text'] = str_replace('<span translate=\'no\'>', '', $translate['text']);
                                $translate['text'] = str_replace('</span>', '', $translate['text']);
                                $translate['text'] = str_replace(';/ ', ';/', $translate['text']);
                                $translate['text'] = str_replace(' &gt', '&gt', $translate['text']);
                                $translate['text'] = str_replace('lt; ', 'lt;', $translate['text']);
                                $translate['text'] = str_replace('&lt; &lt', '&lt;', $translate['text']);
                            }
                            else
                            {
                                $translate["text"] = \html_entity_decode($translate["text"]);
                            }

                            /* compitible with page builder end here */


                            if (isset($translate['status']) && $translate['status'] == 'fail')
                            {
                                $failProducts["failProducts"] = $productId;
                                $msg = __('%1 can\'t be translated for "Product Attribute : %2" .  Error: %3', $product->getName() , $attributeCode, $translate['text']);
                                $this
                                    ->_logger
                                    ->debug("         {$msg}");
                                $this
                                    ->_logger
                                    ->debug("         : characterlimit {$characterLimit}");
                                $this
                                    ->_productAction
                                    ->updateAttributes([$productId], ['translated' => false], $storeId);
                                $_failCount++;
                                continue;
                            }
                            else
                            {
                                if (isset($translate['status']) && $translate['status'] == 'success')
                                {
                                    if ($attributeCode == 'url_key')
                                    {
                                        $urlKey = $this
                                            ->_url
                                            ->formatUrlKey($translate['text']);
                                        $urlKey = str_replace(' ','-', $urlKey);

                                        if ($urlKey != '')
                                        {
                                            $this
                                                ->_productAction
                                                ->updateAttributes([$productId], [$attributeCode => $urlKey, ], $storeId);

                                            $this
                                                ->_productAction
                                                ->updateAttributes([$productId], ['translated' => true, ], $storeId);
                                            $saveRewritesHistory = $this
                                                ->_bizHelper
                                                ->getScopeConfig()
                                                ->isSetFlag(UrlKeyRenderer::XML_PATH_SEO_SAVE_HISTORY, ScopeInterface::SCOPE_STORE, $storeId);
                                            $productModel1 = $this
                                                ->_productModelFactory
                                                ->create();
                                            $_updateProduct = $productModel1->setStoreId($storeId)->load($productId);
                                            if ($_updateProduct->getUrlKey() != $product->getUrlKey())
                                            {
                                                $_updateProduct->setData('save_rewrites_history', $saveRewritesHistory)->save();
                                                $this
                                                    ->urlPersist
                                                    ->replace($this
                                                    ->_productUrlRewrite
                                                    ->generate($_updateProduct));
                                            }
                                        }
                                    }
                                    else
                                    {
                                        if (isset($translate['text']) && $translate['text'] != '')
                                        {
                                            $this
                                                ->_productAction
                                                ->updateAttributes([$productId], [$attributeCode => $translate['text'], ], $storeId);
                                            $this
                                                ->_productAction
                                                ->updateAttributes([$productId], ['translated' => true, ], $storeId);
                                        }
                                    }
                                    $_successCount++;
                                    $this
                                        ->_logger
                                        ->debug("         beforesuccess translate {$productId} : characterlimit {$characterLimit}");
                                    $characterLimit -= mb_strlen($product[$attributeCode]);
                                    $this
                                        ->_logger
                                        ->debug("         aftersuccess translate {$productId} : characterlimit {$characterLimit}");
                                    $this
                                        ->_logger
                                        ->debug("         Save Translation For Batch Product {$productId} and attribute {$attributeCode}");
                                        $_lastSuccessProductId = $productId;
                                }
                                else
                                {
                                    $failProducts["failProducts"] = $productId;
                                    $this
                                        ->_productAction
                                        ->updateAttributes([$productId], ['translated' => false], $storeId);
                                    $_failCount++;
                                    $this
                                        ->_logger
                                        ->debug("          on fail {$productId} : characterlimit {$characterLimit}");
                                }
                            }
                        }
                        catch(\Exception $e)
                        {
                            $failProducts["failProducts"] = $productId;
                            $this
                                ->_logger
                                ->debug($e->getMessage());
                            $this
                                ->_logger
                                ->debug("             Exception on translate : characterlimit {$characterLimit}");
                        }
                    }
                }
                else
                {
                    //set product not translated
                    $this
                        ->_productAction
                        ->updateAttributes([$productId], ['translated' => false], $storeId);
                    $_logCron = $this
                        ->_logCron
                        ->getCollection()
                        ->getLastItem();
                    if ($this
                        ->_date
                        ->gmtDate('d-m-Y') === date('d-m-Y', strtotime($_logCron->getCronDate())))
                    {
                        $this
                            ->_logger
                            ->debug(" Translation Terminated due to characterLimit on translation current remaining charactor is : {$characterLimit} for translating product require charactor is : {$charCount}  ");
                        $this
                            ->_logger
                            ->info('  Daily Limit Reached! For the Day ' . date('d-m-Y H:i:s', time()));
                        $this
                            ->_logCron
                            ->setCronJobCode($jobCode)->setStatus(2)
                            ->setStoreId($storeId)->setRemainLimit($characterLimit)->setProductId($productId)->save();
                        throw new \Exception(__("Daily Limit Reached! Please try again later!"));
                    }
                    else
                    {
                        $_charCutLimit = $this
                            ->_bizHelper
                            ->getConfigValue('translator/general/google_daily_cut_before_limit');
                        $dailyquotalimit = $this
                            ->_bizHelper
                            ->getConfigValue('translator/general/google_daily_limit') - $_charCutLimit;

                        $this
                            ->_logCron
                            ->setCronJobCode($jobCode)->setStatus(1)
                            ->setStoreId($storeId)->setRemainLimit($dailyquotalimit)->setProductId($productId)->save();
                    }
                }
            }
            else
            {
                $_skipCount++;
            }
        }

        $_logCron = $this
            ->_logCron
            ->getCollection()
            ->getLastItem();
        $_lastSuccessProductId = $_lastSuccessProductId > 0 ? $_lastSuccessProductId : $_logCron->getProductId();
        $_charCutLimit = $this
            ->_bizHelper
            ->getConfigValue('translator/general/google_daily_cut_before_limit');
        $characterLimit1 = $this
            ->_bizHelper
            ->getConfigValue('translator/general/google_daily_limit') - $_charCutLimit;
        $remainChar = $characterLimit;
        if ($characterLimit1 == $characterLimit)
        {
            $remainChar = $characterLimit;
        }
        else
        {
            $remainChar = $remainChar > 0 ? $remainChar : 0;
        }
        $cronData1 = $this->_cronData->load($cronId);
        if (($_failCount + $_skipCount) == count($batchProducts))
        {
            $this->logCronEntries($jobCode, $storeId, $remainChar,$cronData1, $_lastSuccessProductId, 1);
        }
        else
        {
            $this->logCronEntries($jobCode, $storeId, $remainChar,$cronData1, $_lastSuccessProductId, 1);
        }
        return $failProducts;
    }

    /**
     * @param   $jobCode
     * @param   $storeId
     * @param   $characterLimit
     * @param   $_lastSuccessProductId
     * @param   $status
     * @return void
     */
    protected function logCronEntries($jobCode, $storeId, $characterLimit,$cronData1,$_lastSuccessProductId = 1, $status = 1)
    {
        $this
            ->_logCron
            ->setCronJobCode($jobCode)->setStatus($status)->setStoreId($storeId)->setRemainLimit($characterLimit)->setProductId($_lastSuccessProductId)->save();
            $cronData1->setLastProductId($_lastSuccessProductId);
            $cronData1->save();
    }
}

