<?php

namespace Biztech\Translator\Block\Adminhtml\Crondata\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Context;
use Biztech\Translator\Helper\Translator;

class CronProduct extends AbstractRenderer
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
        $html = "<a href='" . $this->getUrl('translator/cron/product', ['id'=>$row->getId()]) . "'>";
        $html .= __('View Product');
        $html .= "</a>";

        return $html;
    }
}
