<?php
namespace MageArray\Popup\Block\Adminhtml\Popup;

/**
 * Class Edit
 * @package MageArray\Popup\Block\Adminhtml\Popup
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_objectId = 'popup_id';
        $this->_blockGroup = 'MageArray_Popup';
        $this->_controller = 'adminhtml_popup';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Popup'));
        $this->buttonList->update('delete', 'label', __('Delete'));
        $this->buttonList->add(
            'save_and_continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ],
                    ],
                ],
            ],
            10
        );
    }

    /**
     * @return mixed
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/*/save',
            ['_current' => true, 'back' => 'edit', 'tab' => '{{tab_id}}']
        );
    }
}
