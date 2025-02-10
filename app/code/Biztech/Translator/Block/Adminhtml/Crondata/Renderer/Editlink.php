<?php

namespace Biztech\Translator\Block\Adminhtml\Crondata\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Context;

class Editlink extends AbstractRenderer
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
        $url = $this->getUrl("catalog/product/edit", ["id" => $row->getId(), 'store' => $row->getStoreId()]);
        $html = '<a class="action-menu-item" title="'.__('Edit Product'). '" href="'.$url.'">' . __('Edit') .'</a>';
        return $html;
    }
}
