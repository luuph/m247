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
namespace Bss\RewardPoint\Block\Adminhtml\Customer\Action\Rewardpoints\Edit;

use \Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Bss\RewardPoint\Model\Config\Source\IsActive;

/**
 * Class form
 *
 * Bss\RewardPoint\Block\Adminhtml\Customer\Action\Rewardpoints\Edit
 */
class Form extends Generic
{
    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @var Collection
     */
    protected $customerGroup;

    /**
     * @var IsActive
     */
    protected $isActive;

    /**
     * Form constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param Collection $customerGroup
     * @param IsActive $isActive
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        Collection $customerGroup,
        IsActive $isActive,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->customerGroup = $customerGroup;
        $this->isActive = $isActive;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Class constructor
     *
     * @return void
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
     * @return Generic
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $customerIds = $this->_coreRegistry->registry('customer_ids');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('rate_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'customer_ids',
            'hidden',
            [
                'name' => 'customer_ids',
                'value' => implode(',', $customerIds)
            ]
        );

        $fieldset->addField(
            'website_id',
            'select',
            [
                'label' => __('Website'),
                'title' => __('Website'),
                'name' => 'website_id',
                'required' => true,
                'values' => $this->systemStore->getWebsiteValuesForForm(),
            ]
        );

        $fieldset->addField(
            'point',
            'text',
            [
                'label' => __('Update balance'),
                'title' => __('Update balance'),
                'name' => 'point',
                'note' => __('Enter positive or negative number of points. E.g. 10 or -10'),
                'class' => 'validate-number',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'note',
            'text',
            [
                'label' => __('Note'),
                'title' => __('Note'),
                'name' => 'note',
                'note' => __('')
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
