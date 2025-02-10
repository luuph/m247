<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\Search\Edit;

use Biztech\Translator\Model\Config;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\Setup\Lists;
use Biztech\Translator\Helper\Translator;
use Biztech\Translator\Model\System\Config\Locales;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $storeConfig;
    protected $lists;
    protected $translator;
    protected $locales;
    protected $scopeConfigInterface;

    /**
     * @param Context              $context
     * @param Registry             $registry
     * @param FormFactory          $formFactory
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param Lists                $lists
     * @param Config               $config
     * @param Locales              $locales
     * @param Translator           $translator
     * @param array                $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Lists $lists,
        Config $config,
        Locales $locales,
        Translator $translator,
        array $data = []
    ) {
        $this->locales = $locales;
        $this->scopeConfigInterface = $context->getScopeConfig(); // return \Magento\Framework\App\Config\ScopeConfigInterface;
        $this->translator = $translator;
        $this->storeConfig = $config;
        $this->lists = $lists;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    
    protected function _construct()
    {
        parent::_construct();
        $this->setId('search_edit_form');
        $this->setTitle(__('String Information'));
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('biztech_search');
        $request = $this->getRequest();

        $store = $this->storeConfig->getStoreManager()->getStore($request->getParam('store', 0));

        $url = $this->getUrl('*/*/translateAdmin');

        $translateValues = $this->translator->getTranslateRequestValues($request, $store, $url);
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'search_edit_form',
                    'action' => $this->getUrl('*/*/saveString'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldSet('translate_form', [
            'legend' => __('String Information')
        ]);

        $fieldset->addField(
            'source_label',
            'label',
            [
                'label' => __('Source :'),
                'class' => '',
                'name' => 'source'
            ]
        );

        $fieldset->addField(
            'module',
            'label',
            [
                'label' => __('Module :'),
                'class' => '',
                'name' => 'module'
            ]
        );

        $fieldset->addField(
            'interface',
            'label',
            [
                'label' => __('Interface :'),
                'class' => '',
                'name' => 'interface'
            ]
        );

        $fieldset->addField(
            'store_name',
            'label',
            [
                'label' => __('Store :'),
                'class' => '',
                'name' => 'store_name'
            ]
        );

        $fieldset->addField(
            'original',
            'label',
            [
                'label' => __('Original :'),
                'class' => '',
                'name' => 'module_original'
            ]
        );

        $fieldset->addField(
            'string',
            (isset($translateValues['string']) && strlen($translateValues['string']) > 45 ? 'textarea' : 'text'),
            [
                'label' => __('String :'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'string',
                'after_element_html' => '<p id="translate_error_msg"></p>'
            ]
        );

        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $locales = [];
        if ($storeId == 0) {
            $localeOptions = $this->locales->getFormattedOptionArray();
        } else {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $locale = $this->scopeConfigInterface->getValue('general/locale/code', $storeScope, $storeId);
            array_push($locales, $locale);
            $languages = $this->lists->getLocaleList();
            foreach ($languages as $key => $localeInfo) {
                if (in_array($key, $locales)) {
                    $lang = explode('_', $key);
                    $localelang = explode('(', $localeInfo);
                    $localeOptions[$key] = $localelang[0];
                }
            }
        }
        $fieldset->addField(
            'locale',
            'select',
            [
                'label' => __('Translate To :'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'locale',
                'options' => $localeOptions,
            ]
        );

        $buttonSubmit = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
            'label' => __('Translate'),
            'name' => 'Translate',
            'value' => __('Translate'),
            'id' => 'translate',
            'onclick' => 'BiztechTranslatorForm.adminTranslation(\'' . $this->getUrl('*/*/translateAdmin') . '\')',
            'class' => 'save'
        ]);

        $fieldset->addField(
            'translate_button',
            'note',
            [
                'label' => '',
                'class' => 'button',
                'required' => false,
                'name' => 'submit',
                'text' => $buttonSubmit->toHtml()
            ]
        );

        $fieldset->addField('storeid', 'hidden', [
            'class' => '',
            'name' => 'storeid',
        ]);

        $fieldset->addField('original_translation', 'hidden', [
            'class' => '',
            'name' => 'original_translation'
        ]);

        $fieldset->addField('source', 'hidden', [
            'class' => '',
            'name' => 'source'
        ]);

        $fieldset->addField('translate_url', 'hidden', [
            'class' => '',
            'name' => 'translate_url',
        ]);

        $form->setValues($translateValues);
        return parent::_prepareForm();
    }
}
