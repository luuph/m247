<?php
namespace MageArray\Popup\Block\Adminhtml\Popup\Edit\Tab;

/**
 * Class Settings
 * @package MageArray\Popup\Block\Adminhtml\Popup\Edit\Tab
 */
class Settings extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \MageArray\Popup\Model\Status
     */
    protected $_status;

    /**
     * Settings constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Registry $registry
     * @param \MageArray\Popup\Model\Status $status
     * @param \Magento\Framework\Data\FormFactory $formFactory
     */

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Registry $registry,
        \MageArray\Popup\Model\Status $status,
        \Magento\Framework\Data\FormFactory $formFactory
    ) {
        $this->systemStore = $systemStore;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory);
    }

    /**
     * @return mixed
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(['data' => ['html_id_prefix' => 'page_additional_']]);
        $model = $this->_coreRegistry->registry('popup');
        $isElementDisabled = false;
        $fieldset = $form->addFieldset(
            'Additional_fieldset',
            ['legend' => __('Settings'), 'class' => 'fieldset-wide', 'disabled' => $isElementDisabled]
        );

        $fieldset->addField(
            'showing_frequency',
            'select',
            [
                'name' => 'showing_frequency',
                'label' => __('Showing Frequency'),
                'title' => __('Showing Frequency'),
                'options' => [
                    '0' => __('...'),
                    '1' => __('Show until user closes it'),
                    '2' => __('Show only once'),
                    '3' => __('Show every time'),
                    '4' => __('Show once per session')
                ],
                'note' => "If 'Show only once', popup with the same id 
                won't be shown again until cookie lifetime expires."
            ]
        );

        $fieldset->addField(
            'cookie_time',
            'text',
            [
                'name' => 'cookie_time',
                'label' => __('Cookie lifetime in days'),
                'title' => __('Cookie lifetime in days'),
                'note' => __('You can use also decimal dotted number 
                            (e.g.: To expire cookie in 1 hour put 0.04 which means 1 day divided with 24 hours.)')
            ]
        );

        $fieldset->addField(
            'close_on_overlay_click',
            'select',
            [
                'label' => __('Close when click outside popup'),
                'title' => __('Close when click outside popup'),
                'name' => 'close_on_overlay_click',
                'options' => ['0' => __('Yes'), '1' => __('No')],
                'note' => __('Available for popups with background overlay.')
            ]
        );

        $fieldset->addField(
            'close_on_timeout',
            'text',
            [
                'name' => 'close_on_timeout',
                'label' => __('Close automatically after x seconds'),
                'title' => __('Close automatically after x seconds'),
                'note' => __("Leave 0 or empty if you don't want popup to be closed automatically")
            ]
        );
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    public function getTabLabel()
    {
        return __('Settings');
    }

    /**
     * @return mixed
     */
    public function getTabTitle()
    {
        return __('Settings');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @param $resourceId
     * @return mixed
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
