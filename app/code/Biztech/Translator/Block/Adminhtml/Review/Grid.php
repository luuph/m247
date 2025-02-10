<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\Review;

use Biztech\Translator\Helper\Data;
use Biztech\Translator\Helper\Language;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Review\Helper\Action\Pager;
use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory;
use Magento\Review\Model\ReviewFactory;

class Grid extends \Magento\Review\Block\Adminhtml\Grid
{
    protected $langHelper;
    protected $helperData;

    /**
     * Grid constructor.
     * @param Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param ReviewFactory $reviewFactory
     * @param CollectionFactory $productsFactory
     * @param \Magento\Review\Helper\Data $reviewData
     * @param Pager $reviewActionPager
     * @param Registry $coreRegistry
     * @param Language $langHelper
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        ReviewFactory $reviewFactory,
        CollectionFactory $productsFactory,
        \Magento\Review\Helper\Data $reviewData,
        Pager $reviewActionPager,
        Registry $coreRegistry,
        Language $langHelper,
        Data $helperData,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $reviewFactory,
            $productsFactory,
            $reviewData,
            $reviewActionPager,
            $coreRegistry,
            $data
        );
        $this->langHelper = $langHelper;
        $this->helperData = $helperData;
    }

    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
        if ($this->helperData->isEnabled() && $this->helperData->isTranslatorEnabled()) {
            $languages = $this->langHelper->getLanguages();
            $this->getMassactionBlock()->addItem(
                'translate_all',
                [
                    'label' => __('Translate To'),
                    'url' => $this->getUrl(
                        'translator/translator/massTranslateReview'
                    ),
                    'additional' => [
                        'lang_to' => [
                            'name' => 'lang_to',
                            'type' => 'select',
                            'class' => 'required-entry',
                            'label' => __('Language'),
                            'values' => $languages,
                        ],
                    ]
                ]
            );
        }
    }
}
