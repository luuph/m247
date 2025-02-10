<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/

namespace Biztech\Translator\Helper;

use Biztech\Translator\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Setup\Lists;

class Language extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $jsonEncoder;
    protected $request;
    protected $storeManager;
    protected $scopeConfig;
    protected $lists;
    protected $messageManager;

    /**
     * Language constructor.
     * @param EncoderInterface $jsonEncoder
     * @param Http $request
     * @param Config $config
     * @param Lists $lists
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        EncoderInterface $jsonEncoder,
        Http $request,
        Config $config,
        Lists $lists,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->request = $request;
        $this->storeManager = $config->getStoreManager();
        $this->scopeConfig = $scopeConfig;
        $this->lists = $lists;
        $this->messageManager = $messageManager;
    }

    /**
     * @param $url
     * @param $translatedFields
     * @param $storeId
     * @return string
     */
    public function getConfiguration($url, $translatedFields, $storeId)
    {
        $language = $this->getLanguage($storeId);
        $allLangs = $this->getLanguages();
        $translateBtnText = trim($this->scopeConfig->getValue('translator/general/translate_btntext', \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $storeId));
        if ($language === "no-language") {
            $languageto = [];
        } else {
            $languageto = $allLangs[$language];
        }
        $config = [
            'url' => $url,
            'languageToFullName' => $languageto,
            'fullFromCode' => $this->getFromLanguage(),
            'languageToCode' => $language,
            'fullFromLanguageName' => $this->getFromLangFullName(),
            'translatedFieldsNames' => $translatedFields,
            'translateBtnText' => $translateBtnText ? $translateBtnText : 'Translate To ',
        ];
        return $this->jsonEncoder->encode($config);
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getLanguage($storeId)
    {
        if ($storeId == 0) {
            $localeCode = $this->scopeConfig->getValue('general/locale/code');
        } else {
            $localeCode = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        }
        if ($storeId == 0) {
            $configLang = $this->scopeConfig->getValue('translator/general/languages');
        } else {
            $configLang = $this->scopeConfig->getValue('translator/general/languages', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        }
        $lang = '';
        if ($configLang == 'locale') {
            $arr = explode('_', $localeCode);
            $language = $arr[0];
            if (in_array($language, array_keys($this->getLanguages()))) {
                $lang = $language;
            } else {
                $lang['message'] = __('Select language for this store in System->Configuration->Translator');
            }
        } else {
            $lang = $configLang;
        }
        $language = $lang;
        if (!$language) {
            $language = 'no-language';
        }
        return $language;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        $arrayoflanguages = $this->lists->getLocaleList();
        $languages = [];
        foreach ($arrayoflanguages as $key => $language) {
            $lang = explode('_', $key);
            $valuelang = explode('(', $language);
            $languages[$lang[0]] = $valuelang[0];
        }
        return $languages;
    }

    /**
     * @return mixed
     */
    public function getFromLanguage()
    {
        $storeId = $this->storeManager->getStore()->getStoreId();

        $fromConf = $this->scopeConfig->getValue('translator/general/from_lang', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);

        if ($fromConf == 'auto') {
            $fromLanguage = '';
        } else {
            $fromLanguage = $fromConf;
        }
        return $fromLanguage;
    }

    /**
     * @return mixed|string
     */
    public function getFromLangFullName()
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        $language = $this->getFromLanguage();
        $allLanguages = $this->getLanguages($storeId);
        if ($language) {
            return $allLanguages[$language];
        } else {
            return __('Auto detection');
        }
    }
}
