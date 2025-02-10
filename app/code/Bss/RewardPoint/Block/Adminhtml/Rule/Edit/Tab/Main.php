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

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class main
 *
 * Bss\RewardPoint\Block\Adminhtml\Rule\Edit\Tab
 */
class Main extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $customerGroup;

    /**
     * Main constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->customerGroup = $customerGroup;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Rule Information');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Rule Information');
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
     * Preapre form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('rewardpoint_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rwrule_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

        $type = $this->getRequest()->getParam('type');
        $model->setTypeRule($model->getType());
        if ($type) {
            $model->setTypeRule($type);
        }

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        $fieldset->addField('type_rule', 'hidden', ['name' => 'type_rule']);

        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Rule Name'), 'title' => __('Rule Name'), 'required' => true]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Active'),
                'title' => __('Active'),
                'name' => 'is_active',
                'required' => true,
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'from_date',
            'date',
            [
                'name' => 'from_date',
                'label' => __('From'),
                'title' => __('From'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat
            ]
        );

        $fieldset->addField(
            'to_date',
            'date',
            [
                'name' => 'to_date',
                'label' => __('To'),
                'title' => __('To'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat
            ]
        );

        $fieldset->addField(
            'website_ids',
            'multiselect',
            [
                'label' => __('Websites'),
                'title' => __('Websites'),
                'name' => 'website_ids',
                'required' => true,
                'values' => $this->systemStore->getWebsiteValuesForForm(),
            ]
        );

        $customerGroups = $this->customerGroup->toOptionArray();
        foreach ($customerGroups as $k => $customerGroup) {
            if ($customerGroup['value'] == 0) {
                unset($customerGroups[$k]);
            }
        }

        $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            [
                'name' => 'customer_group_ids',
                'label' => __('Customer Groups'),
                'title' => __('Customer Groups'),
                'required' => true,
                'values' => $customerGroups,
            ]
        );
        if ($type != 'cart') {
            $fieldset->addField(
                'priority',
                'text',
                [
                    'name' => 'priority',
                    'label' => __('Priority'),
                    'title' => __('Priority'),
                    'required' => false
                ]
            );
        }

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
