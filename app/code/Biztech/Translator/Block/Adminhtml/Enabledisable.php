<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Biztech\Translator\Helper\Data;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Store\Model\Website;
use Magento\Store\Model\Store;
use Magento\Framework\View\Asset\Repository;

class Enabledisable extends Field
{
    const XML_PATH_ACTIVATION = 'translator/activation/key';
    protected $_scopeConfig;
    protected $_helper;
    protected $_resourceConfig;
    protected $_web;
    protected $_store;
    protected $_assetRepo;

    public function __construct(
        Context $context,
        Data $helper,
        Config $resourceConfig,
        Website $web,
        Store $store,
        Repository $assetRepo,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->storeManager = $context->getStoreManager();
        $this->_web = $web;
        $this->_resourceConfig = $resourceConfig;
        $this->_store = $store;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_assetRepo = $assetRepo;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $websites = $this->_helper->getAllWebsites();
        if (!empty($websites)) {
            $websiteId = $this->getRequest()->getParam('website', 0);
            if ($websiteId === 0) {
                $storeid = $this->getRequest()->getParam('store', 0);
                if ($storeid === 0) {
                    $html = $element->getElementHtml();
                    return $html;
                }
                $store = $this->_store->load($storeid);
                if ($store && in_array($store->getStoreId(), $websites)) {
                    $html = $element->getElementHtml();
                } else {
                    $html = '<strong class="required" style="color:red;">' . __('Please select store from the activation tab, If store is not listed then Please buy an additional domains') . '</strong>';
                    return $html;
                }
            } else {
                $website = $this->_web->load($websiteId);
                foreach ($website->getGroups() as $group) {
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        if (in_array($store->getStoreId(), $websites)) {
                            $html = $element->getElementHtml();
                            return $html;
                        }
                    }
                }
                $html = '<strong class="required" style="color:red;">' . __('Please select store from the activation tab, If store is not listed then Please buy an additional domains') . '</strong>';
                return $html;
            }
        } else {
            $isSetKey = $this->_scopeConfig->getValue(
                self::XML_PATH_ACTIVATION,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($isSetKey != null || $isSetKey != '') {
                $html = sprintf('<strong class="required" style="color:red;">%s</strong>', __('Please select store from the activation tab, If store is not listed then Please buy an additional domains.'));
                $moduleStatus = $this->_resourceConfig;
                $moduleStatus->saveConfig('translator/general/is_active', 0, 'default', 0);
            } else {
                $html = sprintf('<strong class="required" style="color:red;">%s</strong>', __('Please enter a valid key'));
            }
        }
        return $html;
    }
}
