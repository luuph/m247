<?php
/**
 * Copyright © 2021 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\Translator\Edit\Tab;

use Biztech\Translator\Model\System\Config\Locales;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Biztech\Translator\Model\Config\Source\Storeviewlist;
use Biztech\Translator\Model\Config\Source\Language;
use Magento\Framework\Module\ModuleListInterface;

class StaticdataSerchandtranslate extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $systemStore;
    protected $configLocales;
    protected $language;
    protected $mageModules;


    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Storeviewlist $systemStore,
        Locales $configLocales,
        Language $language,
        ModuleListInterface $mageModules,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->configLocales = $configLocales;
        $this->language = $language;
        $this->mageModules = $mageModules;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'custom_module_search_translate_form',
                    'action' => $this->getUrl('*/*/StaticdataSerchandtranslate', ['id' => $this->getRequest()->getParam('id')]),
                    'method' => 'POST',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldSet('_custom_translator_form', [
            'legend' => __('Translate Module Static Data')
        ]);

        $fieldset->addField('custom_Modules', 'select', [
            'label' => __('Modules'),
            'name' => 'custom_Modules',
            'values' => $this->moduleList(),
            'after_element_html' => '<p class="note">' . __('List of All Modules') . '</p>'
        ]);
        $fieldset->addField(
            'translate_in_store_id',
            'select',
            [
            'name' => 'translate_in_store_id',
            'label' => __('Store'),
            'id' => 'translate_in_store_id',
            'title' => __('Store'),
            'values' => $this->systemStore->toOptionArray(),
            'after_element_html' => '<p class="note">' . __('Select store view to perform Translation') . '</p>'
            ]
        );
        $fieldset->addField(
            'translate_to_language',
            'select',
            [
            'name' => 'translate_to_language',
            'label' => __('Translate to'),
            'id' => 'translate_to_language',
            'title' => __('Translate to'),
            'values' =>  $this->language->toOptionArray(),
            'after_element_html' => '<p class="note">' . __('Select Language To Translate') . '</p>'
            ]
        );

        $buttonSubmit = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
            'label' => __('Translate'),
            'name' => 'custom_search',
            'value' => __('Translate'),
            'id' => 'form_custom_search_submit',
            'onclick' => 'BiztechTranslatorForm.custommatchSearchString(\'' . $this->getUrl('*/*/StaticdataSerchandtranslate') . '\')',
            'class' => 'save'
        ]);

       /* $buttonReset = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
            'label' => __('Reset'),
            'class' => 'back',
            'onclick' => 'BiztechTranslatorForm.customtranslateSearchReset()',
        ]);*/

        $fieldset->addField(
            'submit',
            'note',
            [
                'label' => '',
                'class' => 'button',
                'required' => false,
                'name' => 'submit',
                // 'text' => $buttonReset->toHtml() . $buttonSubmit->toHtml()
                'text' => $buttonSubmit->toHtml()
            ]
        );

        $resultField = $form->addFieldset('custommodulesearchResult', ['legend' => __('Translation Results :')]);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Module Static Data Translate');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Module Static Data Translate');
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
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    protected function moduleList()
    {
        $moduleKeys = array_keys((array)$this->mageModules->getAll());
        foreach ($moduleKeys as $key => $className) {
            $modules[] = ['label' => $className, 'value' => $className];
        }
        return $modules;
    }
}
