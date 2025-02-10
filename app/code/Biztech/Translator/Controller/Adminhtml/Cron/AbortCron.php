<?php

namespace Biztech\Translator\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Biztech\Translator\Model\CrondataFactory;
use Biztech\Translator\Model\MasstranslateinAllstoreFactory;
use Biztech\Translator\Model\MasstranslateNewlyAddedProductsFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class AbortCron extends Action
{
    protected $_masstranslateinAllstoreFactory;
    protected $_masstranslateNewlyAddedProductsFactory;
    protected $_crondataFactory;
    protected $_date;
  
    public function __construct(
        Context $context,
        MasstranslateinAllstoreFactory $masstranslateinAllstoreFactory,
        MasstranslateNewlyAddedProductsFactory $masstranslateNewlyAddedProductsFactory,
        CrondataFactory $crondataFactory,
        DateTime $datetime
    ) {
        parent::__construct($context);
        $this->_masstranslateinAllstoreFactory = $masstranslateinAllstoreFactory;
        $this->_masstranslateNewlyAddedProductsFactory = $masstranslateNewlyAddedProductsFactory;
        $this->_crondataFactory = $crondataFactory;
        $this->_date = $datetime;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($data['abort']) {
            $totalAbort=0;

            /* mass translate in single store pending cron - aborting*/
            $cronTranslate1 = $this->_crondataFactory->create()->getCollection()->addFieldToFilter('status', 'pending');
            if ($cronTranslate1->count() > 0) {
                foreach ($cronTranslate1 as $abortCron1) {
                    $cronDataUpdate = $this->_crondataFactory->create();
                    $abortCron = $cronDataUpdate->load($abortCron1->getId())->setUpdateCronDate($this->_date->gmtDate())->setIsAbort(1)->setStatus('abort')->save();
                    $totalAbort++;
                }
            }

            /* translate product in multiple store pending cron - aborting*/
            $cronTranslate2 = $this->_masstranslateinAllstoreFactory->create()->getCollection()->addFieldToFilter('status', 'pending');
            if ($cronTranslate2->count() > 0) {
                foreach ($cronTranslate2 as $abortCron1) {
                    if ($data['using']==1) {
                        if ($abortCron1->getId()==$data['id']) {
                            continue;
                        }
                    }
                    $cronDataUpdate = $this->_masstranslateinAllstoreFactory->create();
                    $abortCron = $cronDataUpdate->load($abortCron1->getId())->setUpdateCronDate($this->_date->gmtDate())->setIsAbort(1)->setStatus('abort')->save();
                    $totalAbort++;
                }
            }
            /* newly added product to translate pending cron - aborting*/
            $cronTranslate3 = $this->_masstranslateNewlyAddedProductsFactory->create()->getCollection()->addFieldToFilter('status', 'pending');
            if ($cronTranslate3->count() > 0) {
                foreach ($cronTranslate3 as $abortCron1) {
                    $cronDataUpdate = $this->_masstranslateNewlyAddedProductsFactory->create();
                    $abortCron = $cronDataUpdate->load($abortCron1->getId())->setUpdateCronDate($this->_date->gmtDate())->setIsAbort(1)->setStatus('abort')->save();
                    $totalAbort++;
                }
            }

            $this->messageManager->addSuccess(__('<b>'.$totalAbort.'</b> Cron Process have been aborted.'));
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        } else {
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
    }
}
