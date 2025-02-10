<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */

namespace Biztech\Translator\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Biztech\Translator\Model\CrondataFactory;
use Biztech\Translator\Model\MasstranslateinAllstoreFactory;
use Biztech\Translator\Model\MasstranslateNewlyAddedProductsFactory;
use Biztech\Translator\Helper\Data;
use Magento\Framework\Controller\ResultFactory;

class Check extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonFactory;
    /**
     * Crondata model for fatching translation cron related details.
     * @var \Biztech\Translator\Model\Crondata
     */
    protected $_crondataFactory;

    /**
     * MasstranslateinAllstore model for fatching translation cron related details.
     * @var \Biztech\Translator\Model\MasstranslateinAllstore
     */
    protected $_masstranslateinAllstoreFactory;

    /**
     * MasstranslateNewlyAddedProducts model for fatching translation cron related details.
     * @var \Biztech\Translator\Model\MasstranslateNewlyAddedProducts
     */
    protected $_newlyAddedProductTranslateFactory;


    /**
     * @var \Biztech\Translator\Helper\Data
     */
    protected $helper;

    /**
     * @param Context         $context
     * @param JsonFactory     $jsonFactory
     * @param CrondataFactory $crondataFactory
     * @param Data            $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CrondataFactory $crondataFactory,
        MasstranslateinAllstoreFactory $masstranslateinAllstoreFactory,
        MasstranslateNewlyAddedProductsFactory $newlyAddedProductTranslateFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->_jsonFactory = $jsonFactory;
        $this->_crondataFactory = $crondataFactory;
        $this->_masstranslateinAllstoreFactory = $masstranslateinAllstoreFactory;
        $this->_newlyAddedProductTranslateFactory = $newlyAddedProductTranslateFactory;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        if ($this->helper->isTranslatorEnabled()) {
            $result = $this->_jsonFactory->create();
            $_cronModel = $this->_crondataFactory->create()->getCollection()->addFieldToFilter('status', 'pending');
            $_cronModel2 = $this->_masstranslateinAllstoreFactory->create()->getCollection()->addFieldToFilter('status', 'pending');
            $_cronModel3 = $this->_newlyAddedProductTranslateFactory->create()->getCollection()->addFieldToFilter('status', 'pending');

            if ($_cronModel->count() > 0 || $_cronModel2->count() > 0 || $_cronModel3->count() > 0) {
                $result->setData(
                    [
                        'status' => 1,
                        'msg' => __('Cron already exists!')
                    ]
                );
            } else {
                $result->setData(
                    [
                        'status' => 0,
                        'msg' => __('Cron doesn\'t exists!')
                    ]
                );
            }
        } else {
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $this->messageManager->addError(__('Language Translator extension is not enabled. Please enable it from Stores → Configuration → APPJETTY  → Translator → Translator Activation.'));
            $result->setPath('catalog/product/index');
        }

        return $result;
    }
}
