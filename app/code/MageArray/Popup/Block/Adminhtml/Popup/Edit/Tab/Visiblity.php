<?php
namespace MageArray\Popup\Block\Adminhtml\Popup\Edit\Tab;

/**
 * Class Visiblity
 * @package MageArray\Popup\Block\Adminhtml\Popup\Edit\Tab
 */
class Visiblity extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \MageArray\Popup\Model\Status
     */
    protected $_status;

    /**
     * Visiblity constructor.
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
        $form = $this->_formFactory->create(
            [
                'data' =>
                    [
                        'html_id_prefix' => 'page_additional_'
                    ]
            ]
        );
        $model = $this->_coreRegistry->registry('popup');
        $isElementDisabled = false;
        $fieldset = $form->addFieldset(
            'Additional_fieldset',
            [
                'legend' => __('Visiblity'),
                'class' => 'fieldset-wide',
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'store_id',
            'multiselect',
            [
                'name' => 'store_id',
                'label' => __('Showing Frequency'),
                'title' => __('Showing Frequency'),
                'required' => true,
                'values' => $this->systemStore->getStoreValuesForForm(true),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'name' => 'is_active',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'options' => $this->_status->getOptionArray(),
                'disabled' => $isElementDisabled
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
        return __('Visiblity');
    }

    /**
     * @return mixed
     */
    public function getTabTitle()
    {
        return __('Visiblity');
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
