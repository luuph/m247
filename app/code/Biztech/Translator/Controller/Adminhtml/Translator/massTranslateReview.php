<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Controller\Adminhtml\Translator;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Biztech\Translator\Helper\Logger\Logger;
use Biztech\Translator\Helper\Language;
use Biztech\Translator\Helper\Translator;
use Magento\Review\Model\Review;

class massTranslateReview extends \Magento\Backend\App\Action
{

    protected $filter;
    protected $collectionFactory;
    protected $scopeConfig;
    protected $logger;
    protected $langHelper;
    protected $translatorHelper;
    protected $reviewModel;
    protected $translatorModel;


    /**
     * massTranslateReview constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param Language $langHelper
     * @param Translator $translatorHelper
     * @param \Biztech\Translator\Model\Translator $translatorModel
     * @param Review $reviewModel
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        Language $langHelper,
        Translator $translatorHelper,
        \Biztech\Translator\Model\Translator $translatorModel,
        Review $reviewModel
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->langHelper = $langHelper;
        $this->translatorHelper = $translatorHelper;
        $this->reviewModel = $reviewModel;
        $this->translatorModel = $translatorModel;
        parent::__construct($context);
    }

    /**
     * Mass Translation for review.
     * @return json response.
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $reviewIds = $this->getRequest()->getParam('reviews');
        $storeId = $this->getRequest()->getParam('store_id');
        $selectReviewcount = count($reviewIds);
        $translatedReviewCount = 0;
        $languages = $this->langHelper->getLanguages();
        if ($this->getRequest()->getParam('lang_to') != 'locale') {
            $langto = $this->getRequest()->getParam('lang_to');
        } else {
            $langto = $this->translatorHelper->getLanguage($storeId);
        }
        $langFrom = $this->translatorHelper->getFromLanguage($storeId);
        try {
            foreach ($reviewIds as $id) {
                $review = $this->reviewModel->setStoreId($storeId)->load($id);
                if (!$review) {
                    continue;
                }
                $attributeCode = 'detail';
                if (!isset($review[$attributeCode]) || empty($review[$attributeCode])) {
                    continue;
                }
                $translate = $this->translatorModel->getTranslate($review[$attributeCode], $langto, $langFrom);
                if (isset($translate['status']) && $translate['status'] == 'fail') {
                    $this->logger->error('"' . $review->getTitle() . '" can\'t be translate for "Review attribute :' . $attributeCode . '". Error: ' . $translate['text']);
                    $this->messageManager->addError('"' . $review->getTitle() . '" can\'t be translate for "review attribute :' . $attributeCode . '". Error: ' . $translate['text']);
                    continue;
                } else {
                    $review->setData($attributeCode, $translate['text']);
                }
                try {
                    $review->save();
                    if (isset($translate['status']) && $translate['status'] != 'fail') {
                        $translatedReviewCount++;
                    }
                } catch (LocalizedException $e) {
                    $this->logger->debug($e->getRawMessage());
                    continue;
                }
            }
            if ($translatedReviewCount == 0) {
                $this->messageManager->addError(__('Any Review has not been translated. Please see /var/log/translator.log file for detailed information.'));
                return $resultRedirect->setPath('review/product/index');
            } else {
                $langTo = $languages[$langto];
                $this->messageManager->addSuccess(sprintf(' Review(s) of %d has been translated to %s', $selectReviewcount, $langTo));
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getRawMessage());
            $this->messageManager->addError($e->getRawMessage());
            return $resultRedirect->setPath('review/product/index');
        }
        $resultRedirect->setPath('review/product/');
        return $resultRedirect;
    }
}
