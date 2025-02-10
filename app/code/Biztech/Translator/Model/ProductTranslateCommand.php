<?php

namespace Biztech\Translator\Model;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Biztech\Translator\Model\LockableTrait;
use \Symfony\Component\Console\Question\Question;
use Biztech\Translator\Helper\Data as BizHelper;
use Biztech\Translator\Model\CrondataFactory;
use Biztech\Translator\Model\LogcronFactory;
use Biztech\Translator\Model\Logcron;
use Biztech\Translator\Helper\Translator;
use Magento\Catalog\Model\ProductFactory;
use Biztech\Translator\Model\Translator as TranslatorModel;
use Magento\Catalog\Model\Product\Url;
use Magento\CatalogUrlRewrite\Block\UrlKeyRenderer;
use Magento\Store\Model\ScopeInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ProductTranslateCommand extends Command
{
    use LockableTrait;

    protected $_bizHelper;
    protected $_cronDataFactory;
    protected $_logCronFactory;
    protected $_date;
    protected $_logCron;
    protected $_languageHelper;
    protected $_productModelFactory;
    protected $_translatorModel;
    protected $_url;
    protected $_productAction;
    protected $urlPersist;
    protected $_productUrlRewrite;
    protected $_appState;
    protected $_fromLanguage;
    protected $_storeManager;
    protected $timezone;
    protected $_scopeConfig;
    public function __construct(
        BizHelper $bizHelper,
        CrondataFactory $cronDataFactory,
        LogcronFactory $logCronFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        Logcron $_logCron,
        Translator $languageHelper,
        ProductFactory $productFactory,
        TranslatorModel $_translatorModel,
        Url $_url,
        \Magento\Catalog\Model\ResourceModel\Product\Action $action,
        UrlPersistInterface $_urlPersist,
        ProductUrlRewriteGenerator $_productUrlRewrite,
        \Magento\Framework\App\State $appState,
        StoreManagerInterface $storeManager,
        \Biztech\Translator\Model\Config\Source\Fromlanguage $fomLanguage,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timezone
    ) {
        $this->_bizHelper = $bizHelper;
        $this->_cronDataFactory = $cronDataFactory;
        $this->_logCronFactory = $logCronFactory;
        $this->_date = $datetime;
        $this->_logCron = $_logCron;
        $this->_languageHelper = $languageHelper;
        $this->_productModelFactory = $productFactory;
        $this->_translatorModel = $_translatorModel;
        $this->_url = $_url;
        $this->_productAction = $action;
        $this->urlPersist = $_urlPersist;
        $this->_productUrlRewrite = $_productUrlRewrite;
        $this->_appState = $appState;
        $this->_fromLanguage = $fomLanguage;
        $this->_storeManager = $storeManager;
        $this->timezone = $timezone;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct(null);
    }
    protected function configure()
    {
        $this->setName('appjetty:product:translate')
            ->setDescription('Mass product will be translate which products you have added from product grid by option Translate using Console');

        parent::configure();
    }
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!class_exists(\Symfony\Component\Lock\Store\SemaphoreStore::class)) {
            $output->writeln('<error>Please install the symfony/lock module by executing below command in your magento root directory.</error>');
            $output->writeln('
                <fg=black;bg=cyan;options=bold>composer require symfony/lock</>');
            return 0;
        }

        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        // If you prefer to wait until the lock is released, use this:
        // $this->lock(null, true);


        $this->_appState->setAreaCode('adminhtml');
        if (!$this->_bizHelper->isTranslatorEnabled()) {
            $output->writeln('<error>Appjetty Language Translator extension is not enabled. 
Please enable it from <fg=black;bg=cyan;options=bold>Stores → Configuration → AppJetty Extensions → AppJetty Language Translator → Translator Activation.</error>');
            return false;
        }

        $output->writeln('<fg=black;bg=cyan;options=bold>Appjetty Bulk of Products Translator</>
        <info>
        Here you will be able to translate mass product 
        which you have added from catalog grid section by using 
        mass action option → Translate using Console.
        --------------------------------------------------
        </info>');
        $__cronModel = $this->_cronDataFactory->create();
        $__consoleProducts = $__cronModel
            ->getCollection()
            ->addFieldToFilter('status', ['eq' => 'pending'])
            ->addFieldToFilter('is_console', ['eq' => 1]);
        $_pendingProducts = $__consoleProducts->getData();
        if (count($_pendingProducts) == 0) {
            $output->writeln('<comment>Can not find any pending Console Mass Translation for the product.</comment>');
            return 0;
        }
        $this->askTranslate($input, $output);

        // if not released explicitly, Symfony releases the lock
        // automatically when the execution of the command ends
        $this->release();
        return 0;
    }
    public function askTranslate($input, $output)
    {
        try {
            /* get pending product which set for translate using console command*/
            $_cronModel = $this->_cronDataFactory->create();
            $_consoleProducts = $_cronModel
                ->getCollection()
                ->addFieldToFilter('status', ['eq' => 'pending'])
                ->addFieldToFilter('is_console', ['eq' => 1]);
            $_charCutLimit = (int) $this->_bizHelper->getConfigValue('translator/general/google_daily_cut_before_limit');
            $characterLimit = (int) $this->_bizHelper->getConfigValue('translator/general/google_daily_limit') - $_charCutLimit;
            foreach ($_consoleProducts as $consoleProductData) {
                $_logCron = $this->_logCron->getCollection()->getLastItem();
                if (!empty($_logCron->getData())) {
                    if ($this->_date->gmtDate('d-m-Y') == date('d-m-Y', strtotime($_logCron->getCronDate())) && $_logCron->getRemainLimit() <= 0) {
                        /*  Daily Limit Exceed Error. */
                        $output->writeln('<error>
                            Daily Limit Reached! For the Day ' . date('d-m-Y H:i:s', time()) . '</error>');
                        return 0;
                    }
                    /* Remaining limit */
                    if ($this->_date->gmtDate('d-m-Y') == date('d-m-Y', strtotime($_logCron->getCronDate())) && $_logCron->getRemainLimit() > 0) {
                        $characterLimit = $_logCron->getRemainLimit();
                    }
                }
                /*Language To*/
                if ($consoleProductData->getLangTo() == '') {
                    $langTo = $this->_languageHelper->getLanguage($consoleProductData->getStoreId());
                } else {
                    $langTo = $consoleProductData->getLangTo();
                }

                /*Language From*/
                if ($consoleProductData->getLangFrom() == '') {
                    $langFrom = $this->_languageHelper->getFromLanguage($consoleProductData->getStoreId());
                } else {
                    $langFrom = $consoleProductData->getLangFrom();
                }
                $_productIds = json_decode($consoleProductData->getProductIds());
                $output->writeln('<info><fg=green;options=bold>
                You have added total products:</info> ' .
                    count($_productIds) . '<info><fg=green;options=bold>
                You are translating From:</info>' . $this->translateLanguage($langFrom) . '<info><fg=green;options=bold>
                You are translating In:</info>' . $this->translateLanguage($langTo) . '
                <info><fg=green;options=bold>You are translating in Storeview Id:</info> ' . $consoleProductData->getStoreId() . ' <info><fg=green;options=bold>Storeview Name:</info> ' . $this->_storeManager->getStore($consoleProductData->getStoreId())->getName() . '
                <comment>---------------------------------</comment>');
                $jobCode = "consoleProductTranslate";
                $helper = $this->getHelper('question');
                $qTranslate = new Question('Do you want to translate bulk products? (y/n) : ');
                $qTranslateAns = $helper->ask($input, $output, $qTranslate);
                if ($qTranslateAns == 'y') {
                    $batchSize = $this->getBatchsize($input, $output);
                    $output->writeln('<info><fg=green;options=bold>
                    You have selected batch size:</info> ' . $batchSize. '
                    <comment>---------------------------------</comment>');
                    $_batchSize = count(array_chunk($_productIds, $batchSize));
                    $output->writeln('<info>Preparing product translate for the batch.</info>');
                    $sleep = (int)$batchSize / 4;
                    if($sleep<1)
                    {
                        $sleep=1;
                    }
                    sleep($sleep);
                    foreach (array_chunk($_productIds, $batchSize) as $productId) {
                        if ($consoleProductData->getIsAbort() == 0) {
                            if ($characterLimit > 0) {
                                $batchCount = count($productId);
                                $Alredytranslated = $this->batchproductTranslate($consoleProductData->getStoreId(), $langTo, $langFrom, $productId, $characterLimit, $jobCode, $output);
                                if ($Alredytranslated=="exit") {
                                    return 0;
                                }
                                if ($Alredytranslated != null) {
                                    if (count($Alredytranslated) > 0) {
                                        $AlredytranslatedProducts = "<comment>Allready translated products:</comment> ID[";
                                        foreach ($Alredytranslated as $key => $productID) {
                                            $AlredytranslatedProducts .= $productID . ",";
                                        }
                                        $AlredytranslatedProducts = rtrim($AlredytranslatedProducts, ',');
                                        $AlredytranslatedProducts .= "]";
                                        $output->writeln($AlredytranslatedProducts);
                                    }
                                }
                                if ($batchSize < count($_productIds)) {
                                    $_batchSize--;
                                    if ($_batchSize != 0) {
                                        $output->writeln('<info>Preparing next batch for the product translation.</info>');
                                        sleep($sleep);
                                    }
                                }
                            } else {
                                $_logCron = $this->_logCron->getCollection()->getLastItem();
                                if ($this->_date->gmtDate('d-m-Y') == date('d-m-Y', strtotime($_logCron->getCronDate()))) {
                                    $output->writeln('<comment>
                                        Daily Limit Reached! For the Day ' . date('d-m-Y H:i:s', time()) . '</comment>');
                                    break;
                                } else {
                                    $this->_logCron->setCronJobCode($jobCode)
                                        ->setStatus(0)
                                        ->setStoreId($consoleProductData->getStoreId())
                                        ->setRemainLimit($characterLimit)
                                        ->setProductId($productId)
                                        ->save();
                                }
                            }
                        }
                    }
                    if ($this->translateSuccess()) {
                        $date = new \DateTime();
                        $date->setTimestamp($this->timezone->scopeTimeStamp());
                        //$successConsoleData=date('Y-m-d H:i:s', strtotime($this->timezone->scopeTimeStamp()));
                        $successConsoleData=$date->format('Y-m-d H:i:s');
                        //$successConsoleData = strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp());
                        $consoleProductData->setStatus('success')->setUpdateCronDate($successConsoleData)->save();
                    }
                } elseif ($qTranslateAns == 'n') {
                    $output->writeln('Thanks');
                } else {
                    $output->writeln('<error>Please choose correct option.</error>');
                    $this->askTranslate($input, $output);
                   }
            }
        } catch (\Exception $e) {

            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
    public function translateLanguage($languageCode)
    {
        if ($languageCode == null || $languageCode == '') {
            return ' Auto Detect';
        }
        $languages = $this->_fromLanguage->toOptionArray();
        foreach ($languages as $language) {
            if ($language['value'] == $languageCode) {
                return explode(":", $language['label'])[1];
            }
        }
    }
    public function getBatchsize($input, $output)
    {
        $helper = $this->getHelper('question');
        $qbatch = new Question('Enter batch size of the translate product: ');
        $qBatchAns = $helper->ask($input, $output, $qbatch);
        if (preg_match("/[a-zA-Z]/", $qBatchAns)) {
            $output->writeln('<error>Please enter Correct number.</error>');
            $qBatchAns = $this->getBatchsize($input, $output);
        }
        if ((int) $qBatchAns > 0 && (int) $qBatchAns <= 100) {
            return $qBatchAns;
        } else {
            $output->writeln('<error>Please enter size between 1 to 100.</error>');
            $qBatchAns = $this->getBatchsize($input, $output);
        }
        return $qBatchAns;
    }

    protected function batchproductTranslate($storeId, $langTo, $langFrom, $batchProducts, &$characterLimit, $jobCode, $output)
    {
        $_lastSuccessProductId = 0;
        $_failCount = 0;
        $_skipCount = 0;
        $_successCount = 0;
        $remainChar = 0;
        $i = 0;
        $translatedTrue = [];
        foreach ($batchProducts as $batchProduct) {
            $i++;

            if (isset($batchProduct['entity_id'])) {
                $productId = $batchProduct['entity_id'];
            } else {
                $productId = $batchProduct;
            }

            $productModel = $this->_productModelFactory->create();
            $product = $productModel->setStoreId($storeId)->load($productId);

            $attributes = $this->_scopeConfig->getValue('translator/general/massaction_product_translate_fields', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            $translateAll = $this->_bizHelper->getConfigValue('translator/general/translate_all');
            $finalAttributeSet = array_values(explode(',', $attributes));
            if (count($finalAttributeSet) == 0) {
                $output->writeln('<error>Please select product attribute from configuration for the translate.</error>');
                return 0;
            }
            if (($translateAll == 1 && $product->getTranslated() == 1) || ($translateAll == 1 && $product->getTranslated() == 0) || ($translateAll == 0 && $product->getTranslated() == 0)) {
                $charCount = 0;
                foreach ($finalAttributeSet as $attributeCode) {
                    if (!isset($product[$attributeCode]) || empty($product[$attributeCode])) {
                        continue;
                    } else {
                        $charCount += mb_strlen($product[$attributeCode]);
                    }
                }
                $remainChar = $characterLimit - $charCount;
                if ($remainChar > 0) {
                    $_lastSuccessProductId = $productId;

                    foreach ($finalAttributeSet as $attributeCode) {
                        if (!isset($product[$attributeCode]) || empty($product[$attributeCode])) {
                            continue;
                        }

                        try {
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
                                ->_translatorModel->getTranslate($product[$attributeCode], $langTo, $langFrom);
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

                            if (isset($translate['status']) && $translate['status'] == 'fail') {
                                $msg = __('%1 can\'t be translated for "Product Attribute : %2" .  Error: %3', $productId, $attributeCode, $translate['text']);
                                $output->writeln('<error>' . $msg . '</error>');

                                $this->_productAction->updateAttributes([$productId], [
                                    'translated' => false
                                ], $storeId);
                                $_failCount++;
                                return "exit";
                            } else {
                                if (isset($translate['status']) && $translate['status'] == 'success') {
                                    if ($attributeCode == 'url_key') {
                                        $urlKey = $this->_url->formatUrlKey($translate['text']);
                                        $urlKey = str_replace(' ','-', $urlKey);

                                        if ($urlKey != '') {
                                            $this->_productAction->updateAttributes([$productId], [
                                                $attributeCode => $urlKey
                                            ], $storeId);

                                            $this->_productAction->updateAttributes([$productId], [
                                                'translated' => true
                                            ], $storeId);

                                            $saveRewritesHistory = $this->_bizHelper->getScopeConfig()->isSetFlag(
                                                UrlKeyRenderer::XML_PATH_SEO_SAVE_HISTORY,
                                                ScopeInterface::SCOPE_STORE,
                                                $storeId
                                            );

                                            $productModel1 = $this->_productModelFactory->create();
                                            $_updateProduct = $productModel1->setStoreId($storeId)->load($productId);

                                            if ($_updateProduct->getUrlKey() != $product->getUrlKey()) {
                                                $_updateProduct->setData('save_rewrites_history', $saveRewritesHistory)->save();
                                                $this->urlPersist->replace($this->_productUrlRewrite->generate($_updateProduct));
                                            }
                                        }
                                    } else {
                                        if (isset($translate['text']) && $translate['text'] != '') {
                                            $this->_productAction->updateAttributes([$productId], [
                                                $attributeCode => $translate['text']
                                            ], $storeId);
                                            $this->_productAction->updateAttributes([$productId], [
                                                'translated' => true
                                            ], $storeId);
                                        }
                                    }
                                    $_successCount++;
                                    $characterLimit -= mb_strlen($product[$attributeCode]);
                                    $msg = __('Product Id: %1  "Product Attribute : %2" Has Been Translated.', $productId, $attributeCode);
                                    $output->writeln('<comment>' . $msg . '</comment>');
                                } else {
                                    $this->_productAction->updateAttributes(
                                        [$productId],
                                        ['translated' => false],
                                        $storeId
                                    );
                                    $_failCount++;
                                    $msg = __('%1 can\'t be translated for "Product Attribute : %2" .  Error: %3', $productId, $attributeCode, $translate['text']);
                                    $output->writeln('<error>' . $msg . '</error>');
                                    return "exit";
                                }
                            }
                        } catch (\Exception $e) {
                            $output->writeln('<error>Exception on translate characterlimit :</error>' . $characterLimit);
                        }
                    }
                    $product = $productModel->setStoreId($storeId)->load($productId);
                    if ($product->getTranslated() == 1) {
                        $output->writeln('<comment><fg=green;options=bold>Product has been translated successfully. ProductId:</></comment> ' . $productId . '<comment><fg=green;options=bold> Characterlimit: </></comment>' . $characterLimit);
                    } else {
                        $output->writeln('<error>Translation fail on ProductId : </error>' . $productId . '<error> Characterlimit :</error>' . $characterLimit);
                    }
                } else {
                    $_logCron = $this->_logCron->getCollection()->getLastItem();
                    if ($this->_date->gmtDate('d-m-Y') === date('d-m-Y', strtotime($_logCron->getCronDate()))) {
                        $output->writeln('<error>Daily Limit Reached! For the Day ' . date('d-m-Y H:i:s', time()) . '</error>');
                        $this->_logCron->setCronJobCode($jobCode)
                            ->setStatus(2)
                            ->setStoreId($storeId)
                            ->setRemainLimit($characterLimit)
                            ->setProductId($productId)
                            ->save();
                        return "exit";
                    } else {
                        $_charCutLimit = $this->_bizHelper->getConfigValue('translator/general/google_daily_cut_before_limit');
                        $dailyquotalimit = $this->_bizHelper->getConfigValue('translator/general/google_daily_limit') - $_charCutLimit;
                        $this->_logCron->setCronJobCode($jobCode)
                            ->setStatus(1)
                            ->setStoreId($storeId)
                            ->setRemainLimit($dailyquotalimit)
                            ->setProductId(0)
                            ->save();
                    }
                }
            } else {
                $translatedTrue[$_skipCount] = $product->getID();
                $_skipCount++;
            }
        }

        $_logCron = $this->_logCron->getCollection()->getLastItem();
        $_lastSuccessProductId = $_lastSuccessProductId > 0 ? $_lastSuccessProductId : $_logCron->getProductId();
        $_charCutLimit = $this->_bizHelper->getConfigValue('translator/general/google_daily_cut_before_limit');
        $characterLimit1 = $this->_bizHelper->getConfigValue('translator/general/google_daily_limit') - $_charCutLimit;
        $remainChar = $characterLimit;
        if ($characterLimit1 == $characterLimit) {
            $remainChar = $characterLimit;
        } else {
            $remainChar = $remainChar > 0 ? $remainChar : 0;
        }

        if (($_failCount + $_skipCount) == count($batchProducts)) {
            $this->consoleEntries($jobCode, $storeId, $remainChar, $_lastSuccessProductId, 1);
        } else {
            $this->consoleEntries($jobCode, $storeId, $remainChar, $_lastSuccessProductId, 1);
        }
        return $translatedTrue;
    }

    protected function consoleEntries($jobCode, $storeId, $characterLimit, $_lastSuccessProductId = 1, $status = 1)
    {
        $this->_logCron->setCronJobCode($jobCode)
            ->setStatus($status)
            ->setStoreId($storeId)
            ->setRemainLimit($characterLimit)
            ->setProductId($_lastSuccessProductId)
            ->save();
    }

    protected function translateSuccess()
    {
        $cronModel = $this->_cronDataFactory->create();
        $consoleProducts = $cronModel
            ->getCollection()
            ->addFieldToFilter('status', ['eq' => 'pending'])
            ->addFieldToFilter('is_console', ['eq' => 1]);
        foreach ($consoleProducts as $pendingProduct) {
            $pendingProducts = json_decode($pendingProduct->getProductIds());
            foreach ($pendingProducts as $productid) {
                $_productModel = $this->_productModelFactory->create();
                $getProduct = $_productModel->setStoreId($pendingProduct->getStoreId())->load($productid);
                if ($getProduct->getTranslated() == 1) {
                    continue;
                } else {
                    return false;
                }
            }
            return true;
        }
    }
}
