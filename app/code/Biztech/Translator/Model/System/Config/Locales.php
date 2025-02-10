<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/

namespace Biztech\Translator\Model\System\Config;

use Biztech\Translator\Model\Config;
use Magento\Framework\Setup\Lists;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Locales
{
    protected $config;
    protected $lists;
    protected $localeResolver;
    protected $scopeConfigInterface;

    /**
     * @param Config               $config
     * @param Lists                $lists
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param ResolverInterface    $localeResolver
     */
    public function __construct(
        Config $config,
        Lists $lists,
        ScopeConfigInterface $scopeConfigInterface,
        ResolverInterface $localeResolver
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->config = $config;
        $this->lists = $lists;
        $this->localeResolver = $localeResolver;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $locales = [];
        $options = [];
        $options['all'] = __('All');
        $storeManager = $this->config->getStoreManager();
        $languages = $this->lists->getLocaleList();
        foreach ($storeManager->getStores() as $store) {
            $storeScope = ScopeInterface::SCOPE_STORE;
            $locale = $this->scopeConfigInterface->getValue('general/locale/code', $storeScope, $store->getId());
            array_push($locales, $locale);
        }
        foreach ($languages as $key => $localeInfo) {
            if (in_array($key, $locales)) {
                $lang = explode('_', $key);
                $localeLang = explode('(', $localeInfo);
                $options[$key] = $localeLang[0];
            }
        }

        return ($options);
    }

    /**
     * @return array
     */
    public function getFormattedOptionArray()
    {
        $locales = [];
        $options = [];
        $storeManager = $this->config->getStoreManager();
        $languages = $this->lists->getLocaleList();
        foreach ($storeManager->getStores() as $store) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $locale = $this->scopeConfigInterface->getValue('general/locale/code', $storeScope, $store->getId());
            array_push($locales, $locale);
        }
        foreach ($languages as $key => $localeInfo) {
            if (in_array($key, $locales)) {
                $localeLang = explode('(', $localeInfo);
                $options[$key] = $localeLang[0];
            }
        }
        return ($options);
    }
}
