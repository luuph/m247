<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Block\Adminhtml\Campaign\Edit\Tab\Actions;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Tax\Model\TaxClass\Source\Product as TaxProduct;
use Mageplaza\Affiliate\Block\Adminhtml\Campaign\Edit\Tab\Actions\Render\ProductConditions;

/**
 * Class Actions
 * @package Mageplaza\Affiliate\Block\Adminhtml\Campaign\Edit\Tab
 */
class Action extends Generic implements TabInterface
{
    /**
     * Actions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TaxProduct $taxProduct
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TaxProduct $taxProduct,
        array $data = []
    ) {

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $rule = $this->_coreRegistry->registry('current_campaign');

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');

        $actionsFieldset = $form->addFieldset('actions_fieldset', [
            'legend' => __('Apply the rule only to cart items matching the following conditions (leave blank for all items'),
            'class'  => 'fieldset-wide'
        ]);

        $newActionUrl = $this->getUrl(
            'affiliate/condition/NewActionHtml/form/rule_actions',
            ['form_namespace' => 'affiliate_form']
        );

        $actionsFieldset->addField('actions',
            ProductConditions::class, [
                'name'               => 'actions',
            ])->setNewChildUrl($newActionUrl);


        $form->addValues($rule->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
