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
namespace Bss\RewardPoint\Block\Adminhtml\Rate\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Class form
 *
 * Bss\RewardPoint\Block\Adminhtml\Rate\Edit
 */
class Form extends Generic
{
    /**
     * @var \Bss\RewardPoint\Model\Config\Source\Websites
     */
    protected $websites;

    /**
     * @var \Bss\RewardPoint\Model\Config\Source\CustomerGroups
     */
    protected $customerGroups;

    /**
     * @var \Bss\RewardPoint\Model\Config\Source\IsActive
     */
    protected $isActive;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Bss\RewardPoint\Model\Config\Source\Websites $websites
     * @param \Bss\RewardPoint\Model\Config\Source\CustomerGroups $customerGroups
     * @param \Bss\RewardPoint\Model\Config\Source\IsActive $isActive
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Bss\RewardPoint\Model\Config\Source\Websites $websites,
        \Bss\RewardPoint\Model\Config\Source\CustomerGroups $customerGroups,
        \Bss\RewardPoint\Model\Config\Source\IsActive $isActive,
        array $data = []
    ) {
        $this->websites = $websites;
        $this->customerGroups = $customerGroups;
        $this->isActive = $isActive;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Construct
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rate_form');
        $this->setTitle(__('Rate Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('rate_point');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('rate_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('rate_id', 'hidden', ['name' => 'rate_id']);
        }

        $fieldset->addField(
            'website_id',
            'select',
            [
                'name' => 'website_id',
                'label' => __('Website'),
                'title' => __('Website'),
                'required' => true,
                'values' => $this->websites->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'customer_group_id',
            'select',
            [
                'name' => 'customer_group_id',
                'label' => __('Customer Group'),
                'title' => __('Customer Group'),
                'required' => true,
                'values' => $this->customerGroups->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'values' => $this->isActive->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'basecurrency_to_point_rate',
            'text',
            [
                'name' => 'basecurrency_to_point_rate',
                'label' => __('Exchange Rate'),
                'title' => __('Exchange Rate'),
                'class' => 'validate-number validate-greater-than-zero',
                'note' => '10 means that 10 points = $1 (the default base currency is USD)',
                'required' => true,
            ]
        );
        $fieldset->addField(
            'base_currrency_code',
            'hidden',
            [
                'name' => 'base_currrency_code',
                'label' => __('Base Currency Code'),
                'title' => __('Base Currency Code'),
                'readonly' => true,
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
