<?php
namespace Biztech\Translator\Cron;

use Biztech\Translator\Helper\CheckNewAddedProductTranslate\Logger;
use Biztech\Translator\Helper\Data as BizHelper;
use Biztech\Translator\Model\MasstranslateNewlyAddedProductsFactory;
use Biztech\Translator\Model\LogcronFactory;
use Biztech\Translator\Model\Translator;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CheckTranslateNewlyAddedProduct
{
    protected $_bizHelper;
    protected $_date;
    protected $_masstranslateNewlyAddedProductsFactory;
    protected $_logCronFactory;
    protected $_productModelFactory;
    protected $_translatorModel;
    protected $_logger;
    protected $timezone;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param BizHelper                                   $bizHelper
     * @param MasstranslateNewlyAddedProductsFactory                             $MasstranslateNewlyAddedProductsFactory
     * @param LogcronFactory                              $logCronFactory
     * @param ProductFactory                              $productFactory
     * @param Translator                                  $translatorModel
     * @param Logger                                      $logger
     * @param TimezoneInterface                           $timezone
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        BizHelper $bizHelper,
        MasstranslateNewlyAddedProductsFactory $masstranslateNewlyAddedProductsFactory,
        LogcronFactory $logCronFactory,
        ProductFactory $productFactory,
        Translator $translatorModel,
        Logger $logger,
        TimezoneInterface $timezone
    ) {
        $this->_translatorModel = $translatorModel;
        $this->_date = $datetime;
        $this->_bizHelper = $bizHelper;
        $this->_masstranslateNewlyAddedProductsFactory = $masstranslateNewlyAddedProductsFactory;
        $this->_logCronFactory = $logCronFactory;
        $this->_productModelFactory = $productFactory;
        $this->_logger = $logger;
        $this->timezone = $timezone;
    }

    /**
     * @return CheckBizTranslateCron
     */
    public function execute()
    {
        $jobCode = \Biztech\Translator\Model\MasstranslateNewlyAddedProducts::NEWLY_ADDED_PRODUCT_TRANSLATE_CRON_JOB_CODE;
        if (!$this->_bizHelper->isTranslatorEnabled()) {
            throw new \Exception(__("Language Translator extension is not enabled. Please enable it from Stores → Configuration → AppJetty  → Translator → Translator Activation."));
            return false;
        }

        $_logCronModel = $this->_logCronFactory->create();
        $_logCron = $_logCronModel->getCollection()->getLastItem();
        $timescheduled = null;
        
        if ($this->_date->gmtDate('d-m-Y') == date('d-m-Y', strtotime($_logCron->getCronDate()!=null?$_logCron->getCronDate():""))) {
            /* Remaining limit */
            if ($_logCron->getRemainLimit() <= 0) {
                  //$timescheduled = strftime('%Y-%m-%d %H:%M:%S', strtotime($_logCron->getCronDate() . '+1day +1hours'));
                   $date = new \DateTime();
                   $date->setTimestamp(strtotime($_logCron->getCronDate() . '+1day +1hours'));
                   $timescheduled=$date->format('Y-m-d H:i:s');
                //$timescheduled=date('Y-m-d H:i:s', strtotime($_logCron->getCronDate() . '+1day +1hours'));
            }
            if ($_logCron->getCronJobCode() == $jobCode) {
                $this->_logger->info('Translator Check Start');

                $_cronModel = $this->_masstranslateNewlyAddedProductsFactory->create();
                $_cronProducts = $_cronModel
                    ->getCollection()
                    ->addFieldToFilter('status', ['eq' => 'pending']);
                if ($_cronProducts->count() > 0) {
                    $storeIds = json_decode($_cronProducts->getFirstItem()->getStoreIds());
                    $successStores = $_cronProducts->getFirstItem()->getSucceedStoreIds();
                    $success_Store = explode(",", !$successStores=null?$successStores:'');
                    $_pTranslate = $this->getNotTranslatedStore($storeIds, $success_Store);
                    if (is_array($_pTranslate) && !empty($_pTranslate)) {
                        $_checkCronProducts = $this->_masstranslateNewlyAddedProductsFactory->create()->getCollection()->addFieldToFilter('store_ids', ['eq' => json_encode($_pTranslate)])->addFieldToFilter('status', 'pending');
                        if ($_checkCronProducts->count() == 0) {
                            $this->_masstranslateNewlyAddedProductsFactory->create()->load($_cronProducts->getFirstItem()->getId())->setStatus('abort1')->setIsAbort(1)->save();
                            $this->_logger->debug('Cron checked Id: ' . $_cronProducts->getFirstItem()->getId());
                            $this->_logger->debug('new cron create for storeview(s) : ' . implode(
                                ",",
                                $_pTranslate
                            ));

                            try {
                                $_cronUpdate = $this->_masstranslateNewlyAddedProductsFactory->create();
                                $_cronUpdate->setCronName('Cron new product Translation')
                                    ->setStoreIds(json_encode($_pTranslate))
                                    ->setProductIds($_cronProducts->getFirstItem()->getProductIds())
                                    ->setStatus('pending')
                                    ->setLangFrom($_cronProducts->getFirstItem()->getLangFrom())
                                    ->setLangTo($_cronProducts->getFirstItem()->getLangTo())
                                    ->setCronDate($this->_date->gmtDate())
                                    ->setUpdateCronDate($this->_date->gmtDate())
                                    ->save();
                                $this->_translatorModel->setTranslateCron(0, $jobCode, $timescheduled);
                            } catch (\Exception $e) {
                                $this->_logger->debug($e->getMessage());
                            }
                        } else {
                            $this->_translatorModel->setTranslateCron(0, $jobCode, $timescheduled);
                        }
                    } else {
                        if (empty($_pTranslate)) {
                                $this->_logger->info($_cronProducts->getFirstItem()->getId()." Number's Cron translated successfully.");
                                $this->_masstranslateNewlyAddedProductsFactory->create()->load($_cronProducts->getFirstItem()->getId())->setStatus('success')->save();
                                $cronMail_for = $this->_bizHelper->cronMailFor();
                            if (in_array('translatenewlyadded', $cronMail_for)) {
                                $this->_bizHelper->sendEmailNotification("Newly Added Product Translation in Multiple store", $_cronProducts->getFirstItem()->getId(), $_logCron->getRemainLimit());
                            }
                        }
                    }
                }
            }
            $this->_logger->info('Translator Check End');
        } else {
            $_logCronModel = $this->_logCronFactory->create();
            $_logCron = $_logCronModel->getCollection()->getLastItem();
            $_cronModel = $this->_masstranslateNewlyAddedProductsFactory->create();
            $_cronProducts = $_cronModel
                ->getCollection()
                ->addFieldToFilter('status', ['eq' => 'pending']);
            if ($_cronProducts->count() > 0) {
                $date = new \DateTime();

                if (!empty($_logCron->getData()) && $_cronProducts->count() > 0) {
                    if ($this->_date->gmtDate('d-m-Y') == date('d-m-Y', strtotime($_logCron->getCronDate())) && $_logCron->getRemainLimit() <= 0) {
                        //$timescheduled = strftime('%Y-%m-%d %H:%M:%S', strtotime($_logCron->getCronDate() . '+1day +1hours'));
                        $date->setTimestamp(strtotime($_logCron->getCronDate() . '+1day +1hours'));
                        $timescheduled=$date->format('Y-m-d H:i:s');
                        //$timescheduled=date('Y-m-d H:i:s', strtotime($_logCron->getCronDate() . '+1day +1hours'));
                    }
                } elseif (empty($_logCron->getData()) && $_cronProducts->count() > 0) {
                    //$timescheduled = strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp());
                         $date = new \DateTime();
                         $date->setTimestamp($this->timezone->scopeTimeStamp());
                         $timescheduled=$date->format('Y-m-d H:i:s');
                         //$timescheduled=date('Y-m-d H:i:s', strtotime($this->timezone->scopeTimeStamp()));
                } else {
                       //$timescheduled = strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp());
                        $date = new \DateTime();
                        $date->setTimestamp($this->timezone->scopeTimeStamp());
                        $timescheduled=$date->format('Y-m-d H:i:s');
                        //$timescheduled=date('Y-m-d H:i:s', strtotime($this->timezone->scopeTimeStamp()));
                }
            }
            $this->_translatorModel->setTranslateCron(0, $jobCode, $timescheduled);
        }
        return $this;
    }

    protected function getNotTranslatedStore(array $storeId, array $successStore)
    {
        $_store_translate = [];
        $cron_success=true;
        foreach ($storeId as $key => $store_id) {
            if (in_array($store_id, $successStore)) {
                continue;
            } else {
                $_store_translate[] = $store_id;
            }

        }
        return $_store_translate;
    }
}
