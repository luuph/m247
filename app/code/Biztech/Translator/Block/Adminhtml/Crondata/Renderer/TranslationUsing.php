<?php

namespace Biztech\Translator\Block\Adminhtml\Crondata\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Context;

class TranslationUsing extends AbstractRenderer
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
        if ($row->getIsConsole()) {
            $cell = '<b>Console</b>';
        } else {
            $cell = '<b>Cron</b>';
        }
        return $cell;
    }
}
