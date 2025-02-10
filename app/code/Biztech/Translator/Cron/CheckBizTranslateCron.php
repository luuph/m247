<?php

namespace Biztech\Translator\Cron;

use Biztech\Translator\Helper\CronCheck\Logger;
use Biztech\Translator\Helper\Data as BizHelper;
use Biztech\Translator\Model\CrondataFactory;
use Biztech\Translator\Model\LogcronFactory;
use Biztech\Translator\Model\Translator;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CheckBizTranslateCron
{
    protected $_bizHelper;
    protected $_date;
    protected $_cronDataFactory;
    protected $_logCronFactory;
    protected $_productModelFactory;
    protected $_translatorModel;
    protected $_logger;
    protected $timezone;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param BizHelper                                   $bizHelper
     * @param CrondataFactory                             $cronDataFactory
     * @param LogcronFactory                              $logCronFactory
     * @param ProductFactory                              $productFactory
     * @param Translator                                  $translatorModel
     * @param Logger                                      $logger
     * @param TimezoneInterface                           $timezone
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        BizHelper $bizHelper,
        CrondataFactory $cronDataFactory,
        LogcronFactory $logCronFactory,
        ProductFactory $productFactory,
        Translator $translatorModel,
        Logger $logger,
        TimezoneInterface $timezone
    ) {
        $this->_translatorModel = $translatorModel;
        $this->_date = $datetime;
        $this->_bizHelper = $bizHelper;
        $this->_cronDataFactory = $cronDataFactory;
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
        $jobCode = \Biztech\Translator\Model\Crondata::BIZ_CRON_JOB_CODE;
        if (!$this->_bizHelper->isTranslatorEnabled()) {
            //throw new \Exception(__("Language Translator extension is not enabled. Please enable it from Stores → Configuration → AppJetty  → Translator → Translator Activation."));
            return false;
        }
        $_logCronModel = $this->_logCronFactory->create();
        $_logCron = $_logCronModel->getCollection()->getLastItem();
        $_charCutLimit = $this->_bizHelper->getConfigValue('translator/general/google_daily_cut_before_limit');
        $timescheduled = null;
        $storeId = 0;
        
        if ($this->_date->gmtDate('d-m-Y') == date('d-m-Y', strtotime($_logCron->getCronDate()))) {
            /* Remaining limit */
            if ($_logCron->getRemainLimit() <= 0) {
                //$timescheduled = strftime('%Y-%m-%d %H:%M:%S', strtotime($_logCron->getCronDate() . '+1day +1hours'));
                  $date = new \DateTime();
                  $date->setTimestamp(strtotime($_logCron->getCronDate() . '+1day +1hours'));
                  $timescheduled=$date->format('Y-m-d H:i:s');
            }

            if ($_logCron->getCronJobCode() == 'bizgridcrontranslation') {
                $this->_logger->info('Translator Check Start');

                $storeId = $_logCron->getStoreId();
                $_cronModel = $this->_cronDataFactory->create();
                $_cronProducts = $_cronModel
                    ->getCollection()
                    ->addFieldToFilter('status', ['eq' => 'pending'])
                    ->addFieldToFilter('is_console', '0')
                    ->addFieldToFilter('store_id', ['eq' => $storeId]);

                if ($_cronProducts->count() > 0) {
                    $pModel = $this->_productModelFactory->create();

                    $productModel = $pModel->getCollection()
                        ->addStoreFilter($storeId)
                        ->addAttributeToSort('entity_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC)/*->addFieldToFilter('entity_id', ['gt' => $_logCron->getProductId()])*/
                    ;

                    if ($_cronProducts->count()) {
                        $productModel
                            ->addFieldToFilter('entity_id', ['in' => json_decode($_cronProducts->getFirstItem()->getProductIds())]);
                    }

                    $_pTranslate = $this->getNotTranslatedProducts($productModel, $storeId);

                    if (is_array($_pTranslate) && !empty($_pTranslate)) {
                        $_checkCronProducts = $this->_cronDataFactory->create()->getCollection()
                            ->addFieldToFilter('product_ids', ['eq' => json_encode($_pTranslate)])
                            ->addFieldToFilter('status', 'pending')
                            ->addFieldToFilter('is_console', '0');

                        if ($_checkCronProducts->count() == 0) {
                            foreach ($_cronProducts as $_cronProduct) {
                                $this->_cronDataFactory->create()->load($_cronProduct->getId())->setStatus('abort1')->setIsAbort(1)->save();
                            }

                            $langFrom = $_cronProducts->getFirstItem()->getLangFrom() ? $_cronProducts->getFirstItem()->getLangFrom() : null;

                            $this->_logger->debug('Translator Check count of products : ' . count($_pTranslate));
                            $this->_logger->debug('Translator Check: translate to : ' . $_cronProducts->getFirstItem()->getLangTo());
                            $this->_logger->debug('Translator Check: translate from : ' . $langFrom);

                            try {
                                $_cronUpdate = $this->_cronDataFactory->create();
                                $_cronUpdate->setCronName('Cron Translation')
                                    ->setStoreId($storeId)
                                    ->setProductIds(json_encode($_pTranslate))
                                    ->setLangFrom($langFrom)
                                    ->setLangTo($_cronProducts->getFirstItem()->getLangTo())
                                    ->setStatus('pending')
                                    ->setIsConsole(0)
                                    ->save();
                                $this->_translatorModel->setTranslateCron($storeId, $jobCode, $timescheduled);
                            } catch (\Exception $e) {
                                $this->_logger->debug($e->getMessage());
                            }
                        } else {
                            $this->_translatorModel->setTranslateCron($storeId, $jobCode, $timescheduled);
                        }
                    } else {
                        if (empty($_pTranslate)) {
                            foreach ($_cronProducts as $_cronProduct) {
                                $this->_cronDataFactory->create()->load($_cronProduct->getId())->setStatus('success')->save();
                                $cronMail_for = $this->_bizHelper->cronMailFor();
                                if (in_array('translateinsinglestore', $cronMail_for)) {
                                    $this->_bizHelper->sendEmailNotification("Bulk Product Translation", $_cronProduct->getId(), $_logCron->getRemainLimit());
                                }
                            }
                        }
                    }
                }
            }
            $this->_logger->info('Translator Check End');
        } else {
            $_logCronModel = $this->_logCronFactory->create();
            $_logCron = $_logCronModel->getCollection()->getLastItem();
            $_cronModel = $this->_cronDataFactory->create();
            $_cronProducts = $_cronModel
                ->getCollection()
                ->addFieldToFilter('status', ['eq' => 'pending'])
                 ->addFieldToFilter('is_console', '0');

            if ($_cronProducts->count() > 0) {
                 $date = new \DateTime();

                if (!empty($_logCron->getData()) && $_cronProducts->count() > 0) {
                    if ($this->_date->gmtDate('d-m-Y') == date('d-m-Y', strtotime($_logCron->getCronDate())) && $_logCron->getRemainLimit() <= 0) {
                        //$timescheduled = strftime('%Y-%m-%d %H:%M:%S', strtotime($_logCron->getCronDate() . '+1day +1hours'));
                          $date->setTimestamp(strtotime($_logCron->getCronDate() . '+1day +1hours'));
                          $timescheduled=$date->format('Y-m-d H:i:s');
                    }
                } elseif (empty($_logCron->getData()) && $_cronProducts->count() > 0) {
                    $date = new \DateTime();
                    //$timescheduled = strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp());
                    $date->setTimestamp($this->timezone->scopeTimeStamp());
                    $timescheduled=$date->format('Y-m-d H:i:s');
                } else {
                   // $timescheduled = strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp());
                     $date = new \DateTime();
                    $date->setTimestamp($this->timezone->scopeTimeStamp());
                    $timescheduled=$date->format('Y-m-d H:i:s');
                }
            }
            $this->_translatorModel->setTranslateCron($storeId, $jobCode, $timescheduled);
        }
        return $this;
    }

    protected function getNotTranslatedProducts(\Magento\Catalog\Model\ResourceModel\Product\Collection $productModel, int $storeId)
    {
        $_pTranslate = [];
        foreach ($productModel as $product) {
            $pModel1 = $this->_productModelFactory->create();
            $p = $pModel1->setStoreId($storeId)->load($product->getId());
            if ($p->getTranslated() == 1) {
                continue;
            } else {
                $_pTranslate[] = $p->getId();
            }
        }

        sort($_pTranslate);

        return $_pTranslate;
    }
}
