<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\Translator\Edit\Tab;

use Biztech\Translator\Model\System\Config\Locales;
use Biztech\Translator\Model\System\Config\Magemodules;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Search extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $systemStore;
    protected $configLocales;
    protected $mageModules;


    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        Locales $configLocales,
        Magemodules $mageModules,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->configLocales = $configLocales;
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
                    'id' => 'search_translate_form',
                    'action' => $this->getUrl('*/*/translateSearch', ['id' => $this->getRequest()->getParam('id')]),
                    'method' => 'POST',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldSet('translator_form', [
            'legend' => __('Search String &amp; Translate')
        ]);

        $fieldset->addField('searchString', 'text', [
            'label' => __('Search string'),
            'name' => 'searchString',
            'title' => __('Search String'),
            'note' => '<p class="note">' . __('Enter a string (e.g. "Customers")') . '</p>'
        ]);

        $fieldset->addField('locale', 'select', [
            'label' => __('Locale'),
            'name' => 'locale',
            'options' => $this->configLocales->toOptionArray(),
            'after_element_html' => '<p class="note">' . __('Locale of all Stores.') . '</p>'
        ]);

        $fieldset->addField('modules', 'select', [
            'label' => __('Modules'),
            'name' => 'modules',
            'values' => $this->mageModules->toOptionArray(),
            'after_element_html' => '<p class="note">' . __('List of All Modules') . '</p>'
        ]);

        $fieldset->addField(
            'interface',
            'select',
            [
                'label' => __('Interface'),
                'name' => 'interface',
                'values' => $this->mageModules->getInterfaceArray()
            ]
        );

        $buttonSubmit = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
            'label' => __('Search'),
            'name' => 'search',
            'value' => __('Search'),
            'id' => 'form_search_submit',
            'onclick' => 'BiztechTranslatorForm.matchSearchString(\'' . $this->getUrl('*/*/translateSearch') . '\')',
            'class' => 'save'
        ]);

        $buttonReset = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
            'label' => __('Reset'),
            'class' => 'back',
            'onclick' => 'BiztechTranslatorForm.translateSearchReset()',
        ]);

        $fieldset->addField(
            'submit',
            'note',
            [
                'label' => '',
                'class' => 'button',
                'required' => false,
                'name' => 'submit',
                'text' => $buttonReset->toHtml() . $buttonSubmit->toHtml()
            ]
        );

        $resultField = $form->addFieldset('searchResult', ['legend' => __('Search Results :')]);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Search');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Search');
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
}
