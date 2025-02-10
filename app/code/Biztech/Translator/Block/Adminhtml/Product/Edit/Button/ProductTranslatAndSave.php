<?php
namespace Biztech\Translator\Block\Adminhtml\Product\Edit\Button;

use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ProductTranslatAndSave extends GenericButton implements ButtonProviderInterface
{
    
    /**
     * @var \Biztech\Translator\Helper\Data $helperData
     */
    protected $helperData;
     /**
      * @var \Biztech\Translator\Helper\Translator
      */
       protected $translatorhelper;
        /**
         * @var \Magento\Framework\App\RequestInterface $_request
         */
    protected $_request;
    protected $_scopeConfig;
    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Biztech\Translator\Helper\Data $helperData
     * @param \Biztech\Translator\Helper\Translator $translatorhelper
     * @param \Biztech\Translator\Helper\RequestInterface $request
     */
 
    public function __construct(
        \Magento\Backend\Block\Widget\Context               $context,
        \Magento\Framework\Registry                         $registry,
        \Biztech\Translator\Helper\Data $helperData,
        \Biztech\Translator\Helper\Translator $translatorhelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        parent::__construct($context, $registry);
        $this->helperData = $helperData;
        $this->translatorhelper = $translatorhelper;
        $this->_request = $request;
        $this->_scopeConfig = $context->getScopeConfig();
    }
    public function getButtonData()
    {
        if ($this->helperData->isEnabled() && $this->helperData->isTranslatorEnabled()) {
            $storeId = $this->_request->getParam('store', 0);
            $language = $this->translatorhelper->getLanguage($storeId);
            $fullNameLanguage = $this->translatorhelper->getLanguageFullNameByCode($language, $storeId);
            $translateBtnText = trim($this->_scopeConfig->getValue('translator/general/translate_btntext', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId));
            $translateBtnText = $translateBtnText ? $translateBtnText : 'Translate To';
            return [
                    'label' => sprintf('%s: %s', $translateBtnText, $fullNameLanguage),
                    'id' => 'translate_button_all',
                    'title' => __('Translate To'),
                    'on_click' => '',
                    'class' => 'action- scalable action-secondary'
                ];
        }
    }
}
