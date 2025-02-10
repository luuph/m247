<?php

namespace Biztech\Translator\Block\Adminhtml\Crondata\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Context;
use Magento\Store\Model\StoreManagerInterface;

class Storename extends AbstractRenderer
{
   
    protected $_storeManager;

    /**
     * @param Context               $context
     * @param StoreManagerInterface $store
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $store
    ) {
        $this->_storeManager = $store;
        parent::__construct($context);
    }

    public function render(DataObject $row)
    {
        $txtbox = $this->_storeManager->getDefaultStoreView()->getName();

        if ($row->getStoreId() != '' || $row->getStoreId() != '0') {
            $txtbox = $this->_storeManager->getStore()->load($row->getStoreId())->getName();
        }
        if ($row->getStoreId() == "0") {
            $txtbox = __('Translate On All StoreView');
        }
        return $txtbox;
    }
}
