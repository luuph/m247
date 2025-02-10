<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\Catalog\Category;

use Biztech\Translator\Helper\Data;
use Magento\Backend\Block\Widget\Context;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Config\Block\System\Config\Form\Field;
use Biztech\Translator\Helper\Language;

class Edittt extends Field
{
    protected $_template = 'Biztech_Translator::translator/catalog/category/edit.phtml';
    protected $language;
    protected $helperData;

    /**
     * Form constructor.
     * @param Context          $context
     * @param Tree             $tree
     * @param Registry         $registry
     * @param Language         $language
     * @param CategoryFactory  $categoryFactory
     * @param EncoderInterface $encoderInterface
     * @param Data             $helperData
     * @param array            $data
     */
    public function __construct(
        Context $context,
        Tree $tree,
        Registry $registry,
        Language $language,
        CategoryFactory $categoryFactory,
        EncoderInterface $encoderInterface,
        Data $helperData,
        array $data = []
    ) {
        $this->language = $language;
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getCategoryConfiguration()
    {
        if ($this->helperData->isEnabled() && $this->helperData->isTranslatorEnabled()) {
            $storeId = $this->getRequest()->getParam('store', 0);
            $translatedFields = $this->_scopeConfig->getValue('translator/general/massaction_category_translate_fields', \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $storeId);

            $url = $this->getUrl('translator/translator/translate');

            $config = $this->language->getConfiguration($url, $translatedFields, $storeId);

            return $config;
        }
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
