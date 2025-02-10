<?php
namespace MageArray\Popup\Block\Adminhtml\Popup\Edit\Tab;

/**
 * Class CartConditions
 * @package MageArray\Popup\Block\Adminhtml\Popup\Edit\Tab
 */
class CartConditions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \MageArray\Popup\Model\Status
     */
    protected $_status;

    /**
     * CartConditions constructor.
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
                'data' => [
                    'html_id_prefix' => 'page_additional_'
                ]
            ]
        );
        $model = $this->_coreRegistry->registry('popup');
        $isElementDisabled = false;
        $fieldset = $form->addFieldset(
            'Additional_fieldset',
            ['legend' => __('Cart Conditions'), 'class' => 'fieldset-wide', 'disabled' => $isElementDisabled]
        );

        $fieldset->addField(
            'if_pending_order',
            'select',
            [
                'name' => 'if_pending_order',
                'label' => __('If pending payment order'),
                'title' => __('If pending payment order'),
                'options' => [
                    '0' => __('Skip this condition'),
                    '1' => __('Yes, apply this condition')
                ],
                'note' => "Show popup only if current user has any pending payment order in history."
            ]
        );

        $fieldset->addField(
            'product_in_cart',
            'select',
            [
                'name' => 'product_in_cart',
                'label' => __('If product in cart'),
                'title' => __('If product in cart'),
                'options' => [
                    '0' => __('Skip this condition'),
                    '1' => __('Show only if there is any product in cart'),
                    '2' => __('Show only if product cart is empty')
                ]
            ]
        );

        $fieldset->addField(
            'cart_subtotal_min',
            'text',
            [
                'name' => 'cart_subtotal_min',
                'label' => __('Cart subtotal less than'),
                'title' => __('Cart subtotal less than'),
                'note' => __("Leave empty or write 0 if you don't want to apply this condition.")
            ]
        );

        $fieldset->addField(
            'cart_subtotal_max',
            'text',
            [
                'name' => 'cart_subtotal_max',
                'label' => __('Cart subtotal more than'),
                'title' => __('Cart subtotal more than'),
                'note' => __("Leave empty or write 0 if you don't want to apply this condition.")
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
        return __('Cart Conditions');
    }

    /**
     * @return mixed
     */
    public function getTabTitle()
    {
        return __('Cart Conditions');
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
