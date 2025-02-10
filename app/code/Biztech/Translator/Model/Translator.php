<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/
namespace Biztech\Translator\Model;

use Biztech\Translator\Model\Languagetranslator;
use Biztech\Translator\Model\LogcronFactory;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Translator
{
    protected $_scopeConfig;
    protected $_languageTranslate;
    protected $_scheduleFactory;
    protected $_logCronFactory;
    protected $timezone;
    protected $_date;

    /**
     * @param ScopeConfigInterface $config
     * @param \Biztech\Translator\Model\Languagetranslator $languageTranslate
     * @param ScheduleFactory $scheduleFactory
     * @param \Biztech\Translator\Model\LogcronFactory $logCronFactory
     * @param DateTime $datetime
     */
    public function __construct(
        ScopeConfigInterface $config,
        Languagetranslator $languageTranslate,
        ScheduleFactory $scheduleFactory,
        LogcronFactory $logCronFactory,
        DateTime $datetime,
        TimezoneInterface $timezone
    ) {
        $this->_scopeConfig = $config;
        $this->_languageTranslate = $languageTranslate;
        $this->_scheduleFactory = $scheduleFactory;
        $this->_logCronFactory = $logCronFactory;
        $this->_date = $datetime;
        $this->timezone = $timezone;
    }

    /**
     * @param $text
     * @param $langTo
     * @param string $langFrom
     * @return mixed
     */
    public function getTranslate($text, $langTo, $langFrom = '')
    {
        $googleApiKey = $this->_scopeConfig->getValue('translator/general/google_api');
        $sourceData = $text;
        $source = $langTo;
        $target = $langFrom;

        $translator = $this->_languageTranslate->setApiKey($googleApiKey);


        try {
            $targetData = $this->_languageTranslate->translate($sourceData, $source, $target);

        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getRawMessage());
        }

        if ($targetData != '') {

            if (!is_array($targetData) || !array_key_exists('data', $targetData)) {

                $result['text'] = $targetData['error']['message'];
                $result['status'] = 'fail';
                return $result;
            }

            if (!array_key_exists('translations', $targetData['data'])) {
                $result['text'] = $targetData['error']['message'];
                $result['status'] = 'fail';
                return $result;
            }

            if (!is_array($targetData['data']['translations'])) {
                $result['text'] = $targetData['error']['message'];
                $result['status'] = 'fail';
                return $result;
            }

            foreach ($targetData['data']['translations'] as $translation) {
                $translation['translatedText'] = str_replace("<span translate='no'>{{", "{{", $translation['translatedText']);
                $translation['translatedText'] = str_replace("}}</span>", "}}", $translation['translatedText']);
                //$result['text'] = \html_entity_decode($translation['translatedText']);
                $result['text'] = $translation['translatedText'];
                $result['status'] = 'success';
            }
            return $result;
        }
    }

    /**
     * @param int $storeId
     * @param string $jobCode
     * @param null $timescheduled
     * @return bool
     * @throws Exception
     */
    public function setTranslateCron($storeId = 0, $jobCode = 'bizgridcrontranslation', $timescheduled = null)
    {
        $scheduleModel = $this->_scheduleFactory->create();
        $schedule = $scheduleModel->getCollection()->addFieldToFilter('job_code', $jobCode)->load();
        $result = false;

        $_cronModel = $this->_scheduleFactory->create()->getCollection()
        ->addFieldToFilter('job_code', $jobCode)
        ->addFieldToFilter('status', 'pending');
        if ($_cronModel->getData()) {
            return;
        }
        if ($schedule) {
            $result = $this->createCronJob($jobCode, $timescheduled);
        } else {
            try {
                $result = $this->createCronJob($jobCode, $timescheduled);
            } catch (Exception $e) {
                throw new Exception(__('Unable to save Cron expression'));
            }
        }
        return $result;
    }

    /**
     * @param $jobCode
     * @param null $timescheduled
     * @return bool
     * @throws Exception
     */
    protected function createCronJob($jobCode, $timescheduled = null)
    {

        $_charCutLimit = $this->_scopeConfig->getValue('translator/translator_general/google_daily_cut_before_limit');
        $_logCron = $this->_logCronFactory->create()->getCollection()->getLastItem();
        $value_time = !$_logCron->getCronDate()==null ? strtotime($_logCron->getCronDate()) : "";
         // if(!empty($_logCron->getCronDate()))
         // {
         //    $value_time = strtotime($_logCron->getCronDate());
         // }
         // else{
         //    $value_time="";
         // }

        if ($_logCron->getRemainLimit() <= $_charCutLimit && $this->_date->gmtDate('d-m-Y') == date('d-m-Y', strtotime($value_time))) {
            $_cronModel = $this->_scheduleFactory->create()->getCollection()
                ->addFieldToFilter('job_code', $jobCode);

            if (!is_null($timescheduled) || $timescheduled != '') {
                $_cronModel->addFieldToFilter('created_at', ['like' => '%' . date('Y-m-d', strtotime($timescheduled)) . '%']);
            }

            if ($_cronModel->count() != 0) {
                $this->setCronJOB($jobCode, $timescheduled);
                return false;
            }
        }

        $_cronModel = $this->_scheduleFactory->create()->getCollection()
            ->addFieldToFilter('job_code', $jobCode)
            ->addFieldToFilter('status', 'pending');

        if (!is_null($timescheduled) || $timescheduled != '') {
            $_cronModel->addFieldToFilter('created_at', ['like' => '%' . date('Y-m-d', strtotime($timescheduled)) . '%']);
        }

        if ($_cronModel->count() != 0) {
            $this->setCronJOB($jobCode, $timescheduled);
            return false;
        } else {
            if (is_null($timescheduled) || $timescheduled == '') {
            //$timescheduled = strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp());
               $date = new \DateTime();
               $date->setTimestamp($this->timezone->scopeTimeStamp());
               $timescheduled=$date->format('Y-m-d H:i:s');
                //$timescheduled = date('Y-m-d H:i:s', strtotime($this->timezone->scopeTimeStamp()));

            }

            $this->setCronJOB($jobCode, $timescheduled);

            return true;
        }
    }


    private function setCronJOB($jobCode, $timescheduled = null)
    {
               //$timecreated = strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp());
               $date = new \DateTime();
               $date->setTimestamp($this->timezone->scopeTimeStamp());
               $timecreated=$date->format('Y-m-d H:i:s');
               //$timecreated=date('Y-m-d H:i:s', strtotime($this->timezone->scopeTimeStamp()));
        if (is_null($timescheduled) || $timescheduled == '') {
            //$timescheduled = strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp());
            $date = new \DateTime();
            $date->setTimestamp($this->timezone->scopeTimeStamp());
             $timescheduled=$date->format('Y-m-d H:i:s');
             //$timescheduled=date('Y-m-d H:i:s', strtotime($this->timezone->scopeTimeStamp()));
        }
        try {
            $schedule = $this->_scheduleFactory->create();
            $schedule->setJobCode($jobCode)
                ->setCronExpr('0 * */1 * *')
                ->setCreatedAt($timecreated)
                ->setScheduledAt($timescheduled)
                ->setStatus(\Magento\Cron\Model\Schedule::STATUS_PENDING)
                ->save();
        } catch (Exception $e) {
            throw new Exception(__('Unable to save Cron expression'));
        }
    }
}
