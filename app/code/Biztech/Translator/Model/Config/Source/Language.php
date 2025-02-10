<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/
namespace Biztech\Translator\Model\Config\Source;

use Magento\Framework\Setup\Lists;
use Magento\Framework\Locale\ResolverInterface;

class Language implements \Magento\Framework\Option\ArrayInterface
{

    protected $lists;
    protected $localeResolver;
    protected $helperLanguage;

    /**
     * Language constructor.
     * @param Lists                               $lists
     * @param ResolverInterface                   $localeResolver
     * @param \Biztech\Translator\Helper\Language $helperLanguage
     */
    public function __construct(
        Lists $lists,
        ResolverInterface $localeResolver,
        \Biztech\Translator\Helper\Language $helperLanguage
    ) {
        $this->lists = $lists;
        $this->localeResolver = $localeResolver;
        $this->helperLanguage = $helperLanguage;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $languages = $this->helperLanguage->getLanguages();
        $options[] = ['label' => __('Current locale'), 'value' => 'locale'];
        foreach ($languages as $key => $language) {
            $options[] = ['label' => strtoupper($key) . ': ' . $language, 'value' => $key];
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
            $locale = $storeManager->getStore($store->getId())->getConfig('general/locale/code');
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
