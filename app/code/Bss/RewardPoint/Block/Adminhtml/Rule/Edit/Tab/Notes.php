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
 * Class notes
 *
 * Bss\RewardPoint\Block\Adminhtml\Rule\Edit\Tab
 */
class Notes extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Notes constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Notes');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Notes');
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
     * Prepare form
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

        $model = $model->getStoreNotes();

        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset = $this->createStoreSpecificFieldset($form, $model);
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Create store specific field set
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $labels
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createStoreSpecificFieldset($form, $labels)
    {
        $fieldset = $form->addFieldset(
            'store_notes_fieldset',
            ['legend' => __('Store View Specific Notes'), 'class' => 'store-scope']
        );
        $renderer = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset::class
        );
        $fieldset->setRenderer($renderer);

        $fieldset->addField(
            "s_0",
            'textarea',
            [
                'name' => 'store_notes[0]',
                'title' => __('Note for All Store Views'),
                'label' => __('Note for All Store Views'),
                'required' => false,
                'value' => isset($labels[0]) ? $labels[0] : '',
                'fieldset_html_class' => 'store',
                'data-form-part' => 'sales_rule_form'
            ]
        );

        foreach ($this->storeManager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (empty($stores)) {
                    continue;
                }
                foreach ($stores as $store) {
                    $fieldset->addField(
                        "s_{$store->getId()}",
                        'textarea',
                        [
                            'name' => 'store_notes[' . $store->getId() . ']',
                            'title' => $store->getName(),
                            'label' => $store->getName(),
                            'required' => false,
                            'value' => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                            'fieldset_html_class' => 'store',
                            'data-form-part' => 'sales_rule_form'
                        ]
                    );
                }
            }
        }
        return $fieldset;
    }
}
