<?php

namespace Biztech\Translator\Block\Adminhtml\Crondata\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Context;

class TranslationStatus extends AbstractRenderer
{
    protected $_storeManager;

    /**
     * @param Context    $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    public function render(DataObject $row)
    {
        if ($row->getTranslated()) {
            $cell = '<span class="grid-severity-notice"><span> Translated </span></span>';
        } else {
            $cell = '<span class="grid-severity-critical"><span> Not Translated </span></span>';
        }
        return $cell;
    }
}
