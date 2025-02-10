<?php
namespace MageArray\Popup\Block\Adminhtml;

/**
 * Class Popup
 * @package MageArray\Popup\Block\Adminhtml
 */
class Popup extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var
     */
    protected $_controller;
    /**
     * @var
     */
    protected $_blockGroup;
    /**
     * @var
     */
    protected $_headerText;
    /**
     * @var
     */
    protected $_addButtonLabel;

    /**
     *
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_popup';
        $this->_blockGroup = 'MageArray_Popup';
        $this->_headerText = __('Manage Popup');
        $this->_addButtonLabel = __('Add New Popup');
        parent::_construct();
    }
}
