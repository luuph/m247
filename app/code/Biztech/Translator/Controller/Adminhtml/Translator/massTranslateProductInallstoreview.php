<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Controller\Adminhtml\Translator;

use Biztech\Translator\Helper\Data;
use Biztech\Translator\Model\CrondataFactory;
use Biztech\Translator\Model\MasstranslateinAllstoreFactory;
use Biztech\Translator\Model\MasstranslateNewlyAddedProductsFactory;
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
use Biztech\Translator\Helper\Translator as TranslatorHelper;

/**
 * Setting cron for the translation for mass action on the selected products.
 */
class massTranslateProductInallstoreview extends \Magento\Backend\App\Action
{
    protected $filter;
    protected $collectionFactory;
    protected $scopeConfig;
    protected $_logger;
    protected $_bizhelper;
    protected $_masstranslateinAllstoreFactory;
    protected $_masstranslateNewlyAddedProductsFactory;
    protected $_crondataFactory;
    protected $_date;
    protected $_languageHelper;
    protected $_translator;
    protected $_storeManager;
    protected $_translatorHelper;

    /**
     * massTranslateProductInallstoreview constructor.
     * @param Context              $context
     * @param Filter               $filter
     * @param CollectionFactory    $collectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger               $logger
     * @param Data                 $bizhelper
     * @param Language             $languagehelper
     * @param Translator           $translator
     * @param MasstranslateinAllstoreFactory      $masstranslateinAllstoreFactory
     * @param MasstranslateNewlyAddedProductsFactory $masstranslateNewlyAddedProductsFactory
     * @param JsonFactory          $jsonFactory
     * @param Translator           $translatorHelper
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
        MasstranslateinAllstoreFactory $masstranslateinAllstoreFactory,
        MasstranslateNewlyAddedProductsFactory $masstranslateNewlyAddedProductsFactory,
        CrondataFactory $crondataFactory,
        DateTime $datetime,
        StoreManagerInterface $storeManager,
        TranslatorHelper $translatorHelper
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->_bizhelper = $bizhelper;
        $this->_masstranslateinAllstoreFactory = $masstranslateinAllstoreFactory;
        $this->_masstranslateNewlyAddedProductsFactory = $masstranslateNewlyAddedProductsFactory;
        $this->_crondataFactory = $crondataFactory;
        $this->_languageHelper = $languagehelper;
        $this->_translator = $translator;
        $this->_date = $datetime;
        $this->_storeManager = $storeManager;
        $this->_translatorHelper = $translatorHelper;
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
       
        $enable = $this->scopeConfig->getValue('translator/general/is_active');
        if (!$enable) {
            $this->messageManager->addError(__('Please enable Language Translator in <b>Default Config</b> from  <b> Stores → Configuration → AppJetty Extensions → AppJetty Language Translator → Translator Activation.</b>'));
            return $resultRedirect->setPath('catalog/product/index', ['store' => $storeId]);
        }
        $enableTranslateinAllstore = $this->_bizhelper->translateInAllStoreviewEnable();
        $selectedStores = $this->_bizhelper->translateInAllStoreviewEnabledStores();
        if (!$enableTranslateinAllstore) {
            $this->messageManager->addError(__('Please enable <b>Mass Products Translation in Multiple Storeview</b> configuration.'));
            return $resultRedirect->setPath('catalog/product/index', ['store' => $storeId]);
        }
        $_cronModel1 = $this->_crondataFactory->create()->getCollection()->addFieldToFilter('status', 'pending');
        $_cronModel2 = $this->_masstranslateinAllstoreFactory->create()->getCollection()->addFieldToFilter('status', 'pending');
        $_cronModel3 = $this->_masstranslateNewlyAddedProductsFactory->create()->getCollection()->addFieldToFilter('status', 'pending');

        if ($_cronModel1->count() > 0 || $_cronModel2->count() > 0 || $_cronModel3->count() > 0) {
            $pending=true;
        }
        if (isset($ids) && is_array($ids) && !empty($ids)) {
            $TranslatorAdded = $this->setTranslationJob($ids, $selectedStores);
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
            $TranslatorAdded = $this->setTranslationJob($ids, $selectedStores);
        } else {
            $this->messageManager->addError(__('Couldn\'t Register Cron Process! Please try again later'));
        }
        if (isset($TranslatorAdded)) {
            $Storename = "";
            foreach ($selectedStores as $key => $value) {
                $Storename.= ", ". ucfirst($value);
            }
            $Storename = ltrim($Storename, ",");
            if (isset($pending)) {
                $noticeMsg = __(
                    'Selected products has been added to translate in <b>'.$Storename.'</b> storeview. Some cron are running so these added products will be started to translate after complete this ongoing cron, if you want to forcefully abort that cron now then click <b><a href="%1?abort=true&amp;id='.$TranslatorAdded.'&amp;using=1"> Here</a></b>.',
                    $this->getUrl('translator/cron/abortCron')
                );
                $this->messageManager->addNotice($noticeMsg);
            } else {
                $this->messageManager->addSuccess(__('Cron Process Registered for the storeview : <b>'. $Storename.'</b>'));
            }
        }
        return $resultRedirect->setPath('catalog/product/index', ['store' => $storeId]);
    }

    /**
     * create cron process for translating selected product based store.
     * @param array  $ids     productids.
     * @param [type] $storeId storeid to translate product for that store.
     */
    protected function setTranslationJob(array $ids, array $storeData)
    {
        sort($ids);
        $store_ids = array_keys($storeData);
        $to_lang = [];
        $from_lang = [];
        foreach ($store_ids as $key => $store_Id) {
            /*Language To*/
            $to_lang[$store_Id] = $this->_translatorHelper->getLanguage($store_Id);
            /*Language From*/
            $from_lang[$store_Id] = $this->_translatorHelper->getFromLanguage($store_Id);
        }
        try {
            $cronTranslate = $this->_masstranslateinAllstoreFactory->create();
            $cronTranslate->setCronName('Cron Translation')
                ->setStoreIds(json_encode($store_ids))
                ->setProductIds(json_encode($ids))
                ->setStatus('pending')
                ->setLangFrom(json_encode($from_lang))
                ->setLangTo(json_encode($to_lang))
                ->setCronDate($this->_date->gmtDate())
                ->setUpdateCronDate($this->_date->gmtDate());
            $cronTranslate->save();
            $jobCode = $cronTranslate::MASS_TRANSLATE_IN_ALLSTORE_CRON_JOB_CODE;
            $cronSet = $this->_translator->setTranslateCron(0, $jobCode);
            return $cronTranslate->getID();
        } catch (\LocalizedException $e) {
            $this->_logger->debug($e->getRawMessage());
        }
    }
}
