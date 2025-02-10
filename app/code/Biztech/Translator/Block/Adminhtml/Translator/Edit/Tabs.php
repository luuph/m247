<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\Translator\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('biztech_translator_tabs');
        $this->setDestElementId('tab_edit_form');
        $this->setTitle(__('Translator Information'));
    }
}
