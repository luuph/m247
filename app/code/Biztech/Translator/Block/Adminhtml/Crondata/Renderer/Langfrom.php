<?php

namespace Biztech\Translator\Block\Adminhtml\Crondata\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Context;
use Biztech\Translator\Helper\Translator;

class Langfrom extends AbstractRenderer
{
   
    protected $_storeManager;
    protected $_translatorHelper;

    /**
     * @param Context    $context
     * @param Translator $translatorHelper
     */
    public function __construct(
        Context $context,
        Translator $translatorHelper
    ) {
        $this->_translatorHelper = $translatorHelper;
        parent::__construct($context);
    }

    public function render(DataObject $row)
    {
        $txtbox = __('Auto Detect');

        if ($row->getLangFrom() != '') {
            $txtbox = $this->_translatorHelper->getLanguageFullNameByCode($row->getLangFrom(), $row->getStoreId());
        }

        return $txtbox;
    }
}
