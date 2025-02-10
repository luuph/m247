<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

/**
 * Class actions
 *
 * Bss\RewardPoint\Block\Adminhtml\Rule\Edit\Tab
 */
class Actions extends Generic implements TabInterface
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Actions');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('rewardpoint_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rwrule_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        if ($model->getType()) {
            $type = $model->getType();
        } else {
            $type = $this->getRequest()->getParam('type');
        }

        $simple_action = "";
        $spent_amount = "";
        $purchase_point = "";

        if ($type == 'cart') {
            $simple_action = $fieldset->addField(
                'simple_action',
                'select',
                [
                    'label' => __('Type'),
                    'title' => __('Type'),
                    'name' => 'simple_action',
                    'required' => true,
                    'values' => [
                        'fixed' => __('Give customer X points'),
                        'x_for_y' => __('Give customer X points for Y spent')
                    ],
                    'note' =>'Please note that points will be added to the customer account after order is completed.'
                ]
            );

            $spent_amount = $fieldset->addField(
                'spent_amount',
                'text',
                [
                    'name' => 'spent_amount',
                    'label' => __('Spent Amount'),
                    'title' => __('Spent Amount'),
                    'class' => 'validate-number validate-greater-than-zero',
                    'required' => true,
                ]
            );

            $purchase_point = $fieldset->addField(
                'purchase_point',
                'text',
                [
                    'name' => 'purchase_point',
                    'label' => __('Receive points'),
                    'title' => __('Receive points'),
                    'class' => 'validate-number validate-greater-than-zero',
                    'required' => true,
                ]
            );
        }

        $fixed_point = $fieldset->addField(
            'point',
            'text',
            [
                'name' => 'point',
                'label' => __('Receive points'),
                'title' => __('Receive points'),
                'class' => 'validate-number validate-greater-than-zero',
                'required' => true,
            ]
        );

        if ($type == 'cart') {
            $this->setChild(
                'form_after',
                $this->getLayout()->createBlock(
                    \Magento\Backend\Block\Widget\Form\Element\Dependence::class
                )->addFieldMap($simple_action->getHtmlId(), $simple_action->getName())
                 ->addFieldMap($spent_amount->getHtmlId(), $spent_amount->getName())
                 ->addFieldMap($purchase_point->getHtmlId(), $purchase_point->getName())
                 ->addFieldMap($fixed_point->getHtmlId(), $fixed_point->getName())
                 ->addFieldDependence(
                     $spent_amount->getName(),
                     $simple_action->getName(),
                     'x_for_y'
                 )->addFieldDependence(
                     $purchase_point->getName(),
                     $simple_action->getName(),
                     'x_for_y'
                 )
                 ->addFieldDependence(
                     $fixed_point->getName(),
                     $simple_action->getName(),
                     'fixed'
                 )
            );
        }

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
