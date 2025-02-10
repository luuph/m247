<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved.
 */
namespace Biztech\Translator\Controller\Adminhtml\Translator;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Translation\Model\ResourceModel\StringUtils;

class SaveString extends \Magento\Backend\App\Action
{
    protected $cacheManager;
    protected $stringUtils;

    /**
     * @param Context     $context
     * @param StringUtils $cacheManager
     */
    public function __construct(
        Context $context,
        StringUtils $stringUtils,
        CacheInterface $cacheManager
    ) {
        $this->stringUtils = $stringUtils;
        $this->cacheManager = $cacheManager;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $originalString = $this->getRequest()->getParam('original_translation');
        $module = $this->getRequest()->getParam('module');
        $translateString = $this->getRequest()->getParam('string');
        preg_match('#\((.*?)\)#', $this->getRequest()->getParam('source'), $match); //get search string module name.
        $string_module = $match[1];
        $locale = $this->getRequest()->getParam('locale');
        $storeId = $this->getRequest()->getParam('storeid');
        $original = $string_module . '::' . $originalString;
        try {
            $resource = $this->stringUtils->saveTranslate($originalString, $translateString, $locale, $storeId);
            $this->cacheManager->clean();
            $this->messageManager->addSuccess('Translate data is saved.');
        } catch (\Exception $e) {
            $this->messageManager->addException($e->getMessage());
        }
        return $this->_redirect('translator/translator/index');
    }
}
