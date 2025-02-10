<?php
namespace MageArray\Popup\Block\Adminhtml\Popup\Edit;

/**
 * Class Tabs
 * @package MageArray\Popup\Block\Adminhtml\Popup\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('popup_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Popup Information'));
    }
}
