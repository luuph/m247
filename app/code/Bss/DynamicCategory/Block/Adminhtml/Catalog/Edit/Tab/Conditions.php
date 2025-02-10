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
 * @package    Bss_DynamicCategory
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Bss\DynamicCategory\Block\Adminhtml\Catalog\Edit\Tab;

use Bss\DynamicCategory\Model\RuleFactory;
use Bss\DynamicCategory\Model\Rule;
use Bss\DynamicCategory\Model\RuleRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Rule\Block\Conditions as RuleConditions;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Conditions tab
 */
class Conditions extends Generic implements TabInterface
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var RuleConditions
     */
    protected $conditions;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

    /**
     * Intialize conditions
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param RuleConditions $conditions
     * @param Fieldset $rendererFieldset
     * @param RuleFactory $ruleFactory
     * @param RuleRepository $ruleRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RuleConditions $conditions,
        Fieldset $rendererFieldset,
        RuleFactory $ruleFactory,
        RuleRepository $ruleRepository,
        array $data = []
    ) {
        $this->rendererFieldset = $rendererFieldset;
        $this->conditions = $conditions;
        $this->coreRegistry = $registry;
        $this->ruleFactory = $ruleFactory;
        $this->ruleRepository = $ruleRepository;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
    }

    /**
     * Prepare content for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * Retrieve status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Retrieve status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Retrieve tab class
     *
     * @return string
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * Retrieve URL link to tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $category = $this->getCurrentCategory();
        if ($this->getRequest()->getParam('import_conditions_field')) {
            $rule = $this->getRuleDataFromAjax();
        } else {
            $rule = $this->getRuleDataFromCategory($category);
        }
        $form = $this->addTabToForm($rule);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Handles addition of conditions tab to supplied form
     *
     * @param Rule $model
     * @param string $fieldsetId
     * @param string $formName
     * @return Form
     * @throws LocalizedException
     */
    protected function addTabToForm($model, $fieldsetId = 'conditions_fieldset', $formName = 'category_form')
    {
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');
        $model->getConditions();
        $ruleId = $this->getRequest()->getParam('rule_id');

        $url = $this->getUrl(
            'dynamic_category/rule/newConditionHtml/form/' . $model->getConditionsFieldSetId($formName),
            ['form_namespace' => $formName]
        );

        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);

        $renderer = $this->rendererFieldset
            ->setTemplate('Bss_DynamicCategory::fieldset.phtml')
            ->setNewChildUrl($url)
            ->setFieldSetId($conditionsFieldSetId);

        if ($model->getRuleId()) {
            $renderer->setAjaxUrl($this->getUrl(
                'dynamic_category/rule/productpreview',
                ['id' => $ruleId, 'form_key' => $this->formKey->getFormKey(), 'loadGrid' => 1]
            ));
        }

        $fieldset = $form->addFieldset(
            $fieldsetId,
            ['legend' => __('Dynamic Category Rule')]
        )->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name' => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true,
                'data-form-part' => $formName
            ]
        )
            ->setRule($model)
            ->setRenderer($this->conditions);

        $form->setValues($model->getData());
        $this->setConditionFormName(
            $model->getConditions(),
            $formName,
            $conditionsFieldSetId
        );
        return $form;
    }

    /**
     * Sets form name for Condition section.
     *
     * @param AbstractCondition $conditions
     * @param string $formName
     * @param string $jsFormName
     * @return void
     */
    private function setConditionFormName(AbstractCondition $conditions, $formName, $jsFormName)
    {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($jsFormName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }

    /**
     * Retrieve current category model object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * Get rule data when send from ajax
     *
     * @return \Bss\DynamicCategory\Api\Data\RuleInterface|Rule
     */
    public function getRuleDataFromAjax()
    {
        $rule = $this->ruleFactory->create();
        try {
            $rule = $this->ruleRepository->get($this->getRequest()->getParam('import_conditions_field'));
        } catch (\Exception $e) {
            return $rule;
        }
        return $rule;
    }

    /**
     * Get rule data when send from ajax
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return \Bss\DynamicCategory\Api\Data\RuleInterface|Rule
     */
    public function getRuleDataFromCategory($category)
    {
        $rule = $this->ruleFactory->create();
        if ($category->getId()) {
            try {
                $rule = $this->ruleRepository->get($category->getId());
            } catch (\Exception $e) {
                return $rule;
            }
        }
        return $rule;
    }
}
