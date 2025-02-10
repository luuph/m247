<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Controller\Adminhtml\Translator;

use Biztech\Translator\Helper\Data;
use Biztech\Translator\Model\CrondataFactory;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Ui\Component\MassAction\Filter;
use Biztech\Translator\Helper\Language;
use Biztech\Translator\Model\Translator;
use Biztech\Translator\Helper\Logger\Logger;
use Magento\Store\Model\StoreManagerInterface;
use Biztech\Translator\Model\MasstranslateinAllstoreFactory;
use Biztech\Translator\Model\MasstranslateNewlyAddedProductsFactory;

/**
 * Setting cron for the translation for mass action on the selected products.
 */
class massTranslateProductConsole extends \Magento\Backend\App\Action
{
    protected $filter;
    protected $collectionFactory;
    protected $scopeConfig;
    protected $_logger;
    protected $_bizhelper;
    protected $_cronDataFactory;
    protected $_date;
    protected $_languageHelper;
    protected $_translator;
    protected $_storeManager;
    protected $_masstranslateinAllstoreFactory;
    protected $_masstranslateNewlyAddedProductsFactory;

    /**
     * massTranslateProduct constructor.
     * @param Context              $context
     * @param Filter               $filter
     * @param CollectionFactory    $collectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger               $logger
     * @param Data                 $bizhelper
     * @param Language             $languagehelper
     * @param Translator           $translator
     * @param CrondataFactory      $cronDataFactory
     * @param DateTime             $datetime
     * @param StoreManagerInterface $storeManager
     * @param MasstranslateinAllstoreFactory $masstranslateinAllstoreFactory
     * @param MasstranslateNewlyAddedProductsFactory $masstranslateNewlyAddedProductsFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        Data $bizhelper,
        Language $languagehelper,
        Translator $translator,
        CrondataFactory $cronDataFactory,
        DateTime $datetime,
        StoreManagerInterface $storeManager,
        MasstranslateinAllstoreFactory $masstranslateinAllstoreFactory,
        MasstranslateNewlyAddedProductsFactory $masstranslateNewlyAddedProductsFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->_bizhelper = $bizhelper;
        $this->_cronDataFactory = $cronDataFactory;
        $this->_languageHelper = $languagehelper;
        $this->_translator = $translator;
        $this->_date = $datetime;
        $this->_storeManager = $storeManager;
        $this->_masstranslateinAllstoreFactory = $masstranslateinAllstoreFactory;
        $this->_masstranslateNewlyAddedProductsFactory = $masstranslateNewlyAddedProductsFactory;
        parent::__construct($context);
    }


    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $langTo = '';
        $lanFrom = '';
        
        $data = $this->getRequest()->getParams();
        $ids = $this->getRequest()->getParam('selected');
       
        $storeId = 0;
        $filters = (array)$this->getRequest()->getParam('filters', []);
       
        if (isset($filters['store_id'])) {
            $storeId = (int)$filters['store_id'];
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $enable = $this->_bizhelper->enableSiteForStoreview($storeId);
        if (!$enable) {
            $this->messageManager->addError(__('Couldn\'t work for the storeview : <b>'. $this->_storeManager->getStore($storeId)->getName().'</b> | Please enable Language Translator for this storeview from  <b> Stores → Configuration → AppJetty Extensions → AppJetty Language Translator → Translator Activation.</b>'));
            return $resultRedirect->setPath('catalog/product/index', ['store' => $storeId]);
        }
        if ($storeId==0) {
            $istranslateInAllStoreview = $this->_bizhelper->getConfigValue('translator/general/translate_in_all_store_view');
            if ($istranslateInAllStoreview ==0 || $istranslateInAllStoreview==null) {
                $this->messageManager->addError(__('Product can not be translate in all store view. Make sure you have filtered that storeview in which you  wanted to apply translation. incase if you still have to translate for the all storeview not for the spacific storeview you can Enable Translate in all Store view from translator configuration.'));
                return $resultRedirect->setPath('catalog/product/index', ['store' => $storeId]);
            }
        }
        if (isset($ids) && is_array($ids) && !empty($ids)) {
            $this->setTranslationJob($ids, $storeId);
        } elseif ($this->getRequest()->getParam('excluded') == 'false') {
            if (isset($filters)) {
                $filterids = $this->filter->getCollection($this->collectionFactory->create());
                $grid_ids = [];
                foreach ($filterids as $allids) {
                    $grid_ids[] = $allids->getEntityId();
                }
                $ids = $grid_ids;
            } else {
                $ids = $this->collectionFactory->create()->getAllIds();
            }
            $this->setTranslationJob($ids, $storeId);
        } else {
            $this->messageManager->addError(__('Couldn\'t Register Console Process! Please try again later'));
        }
        return $resultRedirect->setPath('catalog/product/index', ['store' => $storeId]);
    }

    /**
     * create cron process for translating selected product based store.
     * @param array  $ids     productids.
     * @param [type] $storeId storeid to translate product for that store.
     */
    protected function setTranslationJob(array $ids, $storeId)
    {
        sort($ids);

        if ($this->getRequest()->getParam('lang_to') != 'locale') {
            $langTo = $this->getRequest()->getParam('lang_to');
        } else {
            $langTo = $this->_languageHelper->getLanguage($storeId);
        }

        $fromConf = $this->scopeConfig->getValue('translator/general/from_lang', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        if ($fromConf == 'auto') {
            $langFrom = '';
        } else {
            $langFrom = $fromConf;
        }
        
        $cronTranslate1 = $this->_cronDataFactory->create();
        $cronTranslate1 = $cronTranslate1->getCollection()->addFieldToFilter('status', 'pending');
        if ($cronTranslate1->count() > 0) {
            foreach ($cronTranslate1 as $abortCron1) {
                $cronDataUpdate = $this->_cronDataFactory->create();
                $abortCron = $cronDataUpdate->load($abortCron1->getId())->setUpdateCronDate($this->_date->gmtDate())->setIsAbort(1)->setStatus('abort')->save();
            }
        } elseif (isset($data['is_abort']) && $data['is_abort'] == 1 && $cronTranslate1->count() > 0) {
            foreach ($cronTranslate1 as $abortCron1) {
                $cronDataUpdate1 = $this->_cronDataFactory->create();
                $abortCron = $cronDataUpdate1->load($abortCron1->getId())->setUpdateCronDate($this->_date->gmtDate())->setIsAbort(1)->setStatus('abort')->save();
            }
        }

        $cronTranslate2 = $this->_masstranslateinAllstoreFactory->create();
        $cronTranslate2 = $cronTranslate2->getCollection()->addFieldToFilter('status', 'pending');
        if ($cronTranslate2->count() > 0) {
            foreach ($cronTranslate2 as $abortCron1) {
                $cronDataUpdate = $this->_masstranslateinAllstoreFactory->create();
                $abortCron = $cronDataUpdate->load($abortCron1->getId())->setUpdateCronDate($this->_date->gmtDate())->setIsAbort(1)->setStatus('abort')->save();
            }
        }
        $cronTranslate3 = $this->_masstranslateNewlyAddedProductsFactory->create();
        $cronTranslate3 = $cronTranslate3->getCollection()->addFieldToFilter('status', 'pending');
        if ($cronTranslate3->count() > 0) {
            foreach ($cronTranslate3 as $abortCron1) {
                $cronDataUpdate = $this->_masstranslateNewlyAddedProductsFactory->create();
                $abortCron = $cronDataUpdate->load($abortCron1->getId())->setUpdateCronDate($this->_date->gmtDate())->setIsAbort(1)->setStatus('abort')->save();
            }
        }
        
        try {
            $cronTranslate = $this->_cronDataFactory->create();
            $cronTranslate->setCronName('Console Translation')
                ->setStoreId($storeId)
                ->setProductIds(json_encode($ids))
                ->setLangFrom($langFrom)
                ->setLangTo($langTo)
                ->setStatus('pending')
                ->setIsConsole(1);
            $cronTranslate->save();
            $this->messageManager->addSuccess(__('Console Process Registered for the storeview : '. $this->_storeManager->getStore($storeId)->getName().' | Make sure you have filtered that storeview in you wanted to translation.'));
        } catch (\LocalizedException $e) {
            $this->_logger->debug($e->getRawMessage());
        }
    }
}
