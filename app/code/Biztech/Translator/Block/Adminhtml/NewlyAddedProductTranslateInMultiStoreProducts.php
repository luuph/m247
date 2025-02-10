<?php

namespace Biztech\Translator\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Biztech\Translator\Helper\Data;

class NewlyAddedProductTranslateInMultiStoreProducts extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected $helper;

    public function __construct(
        Context $context,
        Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_newlyAddedProductTranslateInMultiStoreProducts';/*block grid.php directory*/
        $this->_blockGroup = 'Biztech_Translator';
        if ($this->helper->isTranslatorEnabled()) {
            parent::_construct();
        }
        $this->removeButton('add');
    }
}
