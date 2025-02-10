<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/

namespace Biztech\Translator\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Translator extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $config;
    protected $helperLang;

    /**
     * @param ScopeConfigInterface                $config
     * @param \Biztech\Translator\Helper\Language $helperLang
     */
    public function __construct(
        ScopeConfigInterface $config,
        \Biztech\Translator\Helper\Language $helperLang
    ) {
        $this->config = $config;
        $this->helperLang = $helperLang;
    }

    /**
     * @param $storeId
     * @return mixed|string
     */
    public function getFromLanguage($storeId)
    {
        $fromConf = $this->config->getValue('translator/general/from_lang', ScopeInterface::SCOPE_STORE, $storeId);

        if ($fromConf == 'auto') {
            $fromLanguage = '';
        } else {
            $fromLanguage = $fromConf;
        }
        return $fromLanguage;
    }

    /**
     * @param $code
     * @param $storeId
     * @return bool
     */
    public function getLanguageFullNameByCode($code, $storeId)
    {
        $languagesList = $this->helperLang->getLanguages();
        if ($code == 'locale') {
            $lang = $this->getLanguage($storeId);
            if (is_array($lang)) {
                return false;
            } else {
                return $languagesList[$lang];
            }
        }
        return $languagesList[$code];
    }

    /**
     * @param $storeId
     * @return mixed|string
     */
    public function getLanguage($storeId)
    {
        if ($storeId == 0) {
            $localeCode = $this->config->getValue('general/locale/code');
        } else {
            $localeCode = $this->config->getValue('general/locale/code', ScopeInterface::SCOPE_STORE, $storeId);
        }
        if ($storeId == 0) {
            $configLang = $this->config->getValue('translator/general/languages');
        } else {
            $configLang = $this->config->getValue('translator/general/languages', ScopeInterface::SCOPE_STORE, $storeId);
        }
        $lang = '';
        if ($configLang == 'locale') {
            $arr = explode('_', $localeCode);
            $language = $arr[0];
            if (in_array($language, array_keys($this->helperLang->getLanguages()))) {
                $lang = $language;
            } else {
                $lang['message'] = __('Select language for this store in System->Configuration->Translator');
            }
        } else {
            $lang = $configLang;
        }
        return $lang;
    }

    /**
     * @param $request
     * @param $store
     * @param $url
     * @return array
     * @codingStandardsIgnoreStart
     */
    public function getTranslateRequestValues($request, $store, $url)
    {
        $values = [];
        $values['module'] = ucfirst($request->getParam('modules'));
        $translation = explode('::', base64_decode($request->getParam('translation')));
        $values['string'] = (isset($translation[1])) ? htmlspecialchars_decode($translation[1]) : htmlspecialchars_decode(base64_decode($request->getParam('translation')));
        $values['original_translation'] = htmlspecialchars_decode(base64_decode($request->getParam('original')));
        $original = explode('::', $values['original_translation']);
        $values['original'] = (isset($original[1]) ? $original[1] : $original[0]);
        $values['source'] = base64_decode($request->getParam('source'));
        $values['source_label'] = base64_decode($request->getParam('source'));
        $values['interface'] = ucfirst($request->getParam('interface'));
        $values['locale'] = $request->getParam('locale');
        $values['storeid'] = $store->getId();
        $values['store_name'] = $store->getId() != 0 ? $store->getName() : 'Main Website';
        $values['translate_url'] = $url;
        return $values;
    }
}
