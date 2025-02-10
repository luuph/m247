<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/

namespace Biztech\Translator\Model\System\Config;

use Magento\Store\Model\ScopeInterface;
use Biztech\Translator\Model\Config;
use Magento\Framework\Setup\Lists;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class LocaleString
{
    protected $scopeConfigInterface;
    protected $config;
    protected $lists;
    protected $localeResolver;

    
    /**
     * LocaleString constructor.
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
        $storeManager = $this->config->getStoreManager();
        $languages = $this->lists->getLocaleList();

        foreach ($storeManager->getStores() as $store) {
            $storeScope = ScopeInterface::SCOPE_STORES;
            $locale = $this->scopeConfigInterface->getValue('general/locale/code', $storeScope, $store->getId());
            array_push($locales, $locale);
        }

        foreach ($languages as $key => $localeInfo) {
            if (in_array($key, $locales)) {
                $options[$key] = $localeInfo;
            }
        }
        return ($options);
    }
}
