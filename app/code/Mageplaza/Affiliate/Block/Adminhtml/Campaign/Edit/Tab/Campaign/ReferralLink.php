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

namespace Mageplaza\Affiliate\Block\Adminhtml\Campaign\Edit\Tab\Campaign;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Actions;
use Mageplaza\Affiliate\Block\Adminhtml\Campaign\Edit\Tab\Campaign\Render\Link;
use Mageplaza\Affiliate\Model\Campaign\Discount as CampaignDiscount;

/**
 * Class Discount
 * @package Mageplaza\Affiliate\Block\Adminhtml\Campaign\Edit\Tab
 */
class ReferralLink extends Generic implements TabInterface
{
    /**
     * @var Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var Actions
     */
    protected $_ruleActions;

    /**
     * @var Yesno
     */
    protected $_boolean;

    /**
     * @var CampaignDiscount
     */
    protected $_discount;

    /**
     * Discount constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $boolean
     * @param Actions $ruleActions
     * @param CampaignDiscount $discount
     * @param Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $boolean,
        Actions $ruleActions,
        CampaignDiscount $discount,
        Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_ruleActions = $ruleActions;
        $this->_boolean = $boolean;
        $this->_discount = $discount;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Discounts');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Discounts');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_campaign');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('refer_');

        $fieldset = $form->addFieldset('referral_url', ['legend' => __('Campaign Referral Link')]);
        $form->setValues($model->getData());

        $fieldset->addField('referral_link', 'text', [
            'name' => 'referral_link',
            'label' => __('Referral Link'),
            'title' => __('Referral Link'),
            'required' => true,
            'note'   => __('Please fill in the referral link you want to create for the campaign. 
            If left blank will default to the only link placed in the Configuration section.'),
            'class' => 'validate-url',
            'value' => $model->getReferralLink() ?? $this->getBaseUrl()
        ]);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
