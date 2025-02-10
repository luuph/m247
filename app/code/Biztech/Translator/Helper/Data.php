<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/
namespace Biztech\Translator\Helper;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLED = 'translator/general/is_active';
    const XML_PATH_INSTALLED = 'translator/activation/installed';
    const XML_PATH_DATA = 'translator/activation/data';
    const XML_PATH_WEBSITES = 'translator/activation/websites';
    const XML_PATH_EN = 'translator/activation/en';
    const XML_PATH_KEY = 'translator/activation/key';

    protected $_resourceConfig;
    protected $encryptor;
    protected $coreConfig;
    protected $_store;
    protected $_storeManager;
    protected $logger;
    protected $_website;
    protected $_transportBuilder;

    /**
     * Data constructor.
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param ModuleListInterface $moduleList
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Config $resourceConfig
     * @param ReinitableConfigInterface $coreConfig
     * @param StoreManagerInterface $store
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        ModuleListInterface $moduleList,
        StoreManagerInterface $storeManager,
        Config $resourceConfig,
        ReinitableConfigInterface $coreConfig,
        \Magento\Store\Model\StoreManagerInterface $store,
        Website $_website,
        TransportBuilder $transportBuilder
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->logger = $context->getLogger();
        $this->_storeManager = $storeManager;
        $this->_resourceConfig = $resourceConfig;
        $this->encryptor = $encryptor;
        $this->coreConfig = $coreConfig;
        $this->_store = $store;
        $this->_website = $_website;
        $this->_transportBuilder = $transportBuilder;
        parent::__construct($context);
    }

    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    public function getConfigValue($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $query
     * @return string
     */
    public function buildHttpQuery($query)
    {
        $query_array = [];
        foreach ($query as $key => $key_value) {
            $query_array[] = $key . '=' . urlencode($key_value);
        }
        return implode('&', $query_array);
    }

    /**
     * @param $xmlString
     * @return array
     */
    public function parseXml($xmlString)
    {
        libxml_use_internal_errors(true);
        $xmlObject = simplexml_load_string($xmlString);
        $result = [];
        if (!empty($xmlObject)) {
            $this->convertXmlObjToArr($xmlObject, $result);
        }
        return $result;
    }

    /**
     * @param $obj
     * @param $arr
     */
    public function convertXmlObjToArr($obj, &$arr)
    {
        $children = $obj->children();
        $executed = false;
        foreach ($children as $elementName => $node) {
            if (is_array($arr) && array_key_exists($elementName, $arr)) {
                if (is_array($arr[$elementName]) && array_key_exists(0, $arr[$elementName])) {
                    $i = count($arr[$elementName]);
                    $this->convertXmlObjToArr($node, $arr[$elementName][$i]);
                } else {
                    $tmp = $arr[$elementName];
                    $arr[$elementName] = [];
                    $arr[$elementName][0] = $tmp;
                    $i = count($arr[$elementName]);
                    $this->convertXmlObjToArr($node, $arr[$elementName][$i]);
                }
            } else {
                $arr[$elementName] = [];
                $this->convertXmlObjToArr($node, $arr[$elementName]);
            }
            $executed = true;
        }
        if (!$executed && $children->getName() == "") {
            $arr = (String)$obj;
        }
        return;
    }

    /**
     * @return array
     */
    public function getAllStoreDomains()
    {
        $domains = [];
        foreach ($this->_storeManager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $url = $store->getConfig('web/unsecure/base_url');
                    if ($domain = trim(preg_replace('/^.*?\/\/(.*)?\//', '$1', $url))) {
                        $domains[] = $domain;
                    }
                    $url = $store->getConfig('web/secure/base_url');
                    if ($domain = trim(preg_replace('/^.*?\/\/(.*)?\//', '$1', $url))) {
                        $domains[] = $domain;
                    }
                }
            }

        }
        return array_unique($domains);
    }

    /**
     * @return mixed
     *
     */
    public function getDataInfo()
    {
        //@codingStandardsIgnoreStart
        $data = $this->scopeConfig->getValue(self::XML_PATH_DATA, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return json_decode(base64_decode($this->encryptor->decrypt($data)));
        //@codingStandardsIgnoreEnd
    }

    /**
     * @return array
     */
    public function getAllWebsites()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_INSTALLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$value) {
            return [];
        }
        $data = $this->scopeConfig->getValue(self::XML_PATH_DATA, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $web = $this->scopeConfig->getValue(self::XML_PATH_WEBSITES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $websites = explode(',', str_replace($data ?? '', '', $this->encryptor->decrypt($web)));
        $websites = array_diff($websites, [""]);
        return $websites;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function getFormatUrl($url)
    {
        $input = trim($url, '/');
        if (!preg_match('#^http(s)?://#', $input)) {
            $input = 'http://' . $input;
        }
        //@codingStandardsIgnoreStart
        $urlParts = parse_url($input);
        //@codingStandardsIgnoreEnd
        if (isset($urlParts['path'])) {
            $domain = preg_replace('/^www\./', '', $urlParts['host'] . $urlParts['path']);
        } else {
            $domain = preg_replace('/^www\./', '', $urlParts['host']);
        }
        return $domain;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $StoreID = $this->_store->getStore()->getId();
        $isenabled = $this->scopeConfig->getValue(self::XML_PATH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($isenabled) {
            if ($StoreID) {
                $allStores = $this->getAllWebsites();
                $key = $this->scopeConfig->getValue(self::XML_PATH_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($key == null || $key == '') {
                    return false;
                } else {
                    $en = $data = $this->scopeConfig->getValue(self::XML_PATH_EN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    if ($isenabled && $en && in_array($StoreID, $allStores)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                $en = $en = $data = $this->scopeConfig->getValue(self::XML_PATH_EN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($isenabled && $en) {
                    return true;
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function isTranslatorEnabled()
    {
        $storeId = $this->_store->getStore()->getId();
        $isEnabled = $this->scopeConfig->getValue('translator/general/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        if ($isEnabled) {
            return true;
        }
        return false;
    }

    public function isUrlKeyAttribute()
    {
        $flag = false;
        $translateAll = $this->scopeConfig->getValue('translator/general/massaction_product_translate_fields');
        $finalAttributeSet = array_values(explode(',', $translateAll));

        if (in_array('url_key', $finalAttributeSet)) {
            $flag = true;
        }
        
        return $flag;
    }

    /**
     * @return boolean
     * 18-11-2019 | - specific storeview activation checking
     */
    public function enableSiteForStoreview($store_id)
    {
        $allStores = $this->getAllWebsites();
        if (!empty($allStores)) {
            if (in_array($store_id, $allStores)) {
                if ($this->scopeConfig->getValue(self::XML_PATH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                if ($store_id == 0) {
                    if ($this->scopeConfig->getValue(self::XML_PATH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id)) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /*mass product translate in multiple storeview*/
    public function translateInAllStoreviewEnable()
    {
        return $this->scopeConfig->getValue('translator/general/translator_mass_product_in_allstoreview');
    }

    public function translateInAllStoreviewEnabledStores()
    {
        $storeData=[];
        $store_ids =$this->scopeConfig->getValue('translator/general/mass_product_store');
        $storeIdList = explode(",", $store_ids==null ? "" : $store_ids);
        foreach ($storeIdList as $key => $id) {
            $storeData[$id]=$this->_storeManager->getStore($id)->getName();
        }
        return $storeData;
    }
    /*end*/

    /*new added products translate in multiple storeview*/
    public function newAddedProductTranslateEnable()
    {
        return $this->scopeConfig->getValue('translator/general/translator_newly_added_product_in_allstoreview');
    }

    public function newAddedProductTranslateEnabledStores()
    {
        $storeData=[];
        $store_ids =$this->scopeConfig->getValue('translator/general/newly_added_product_store');
        $storeIdList = explode(",", $store_ids);
        foreach ($storeIdList as $key => $id) {
            $storeData[$id]=$this->_storeManager->getStore($id)->getName();
        }
        return $storeData;
    }

    /* Old added product will be considered from the module installation date*/
    public function oldAddedProductTranslateEnable()
    {
        return $this->scopeConfig->getValue('translator/general/old_added_product');
    }

    public function moduleInstallDate()
    {
        return $this->scopeConfig->getValue('translator/general/module_installed_date');
    }

    /*Date of enabled feature "Translate new added product"*/
    public function newAddedProductDate()
    {
        return $this->scopeConfig->getValue('translator/general/newly_added_product_date');
    }
    /*end*/

    /*send mail for corn"*/
    public function cronMailFor()
    {
        $mailFor = $this->scopeConfig->getValue('translator/general/cron_mail_for');
        return explode(",", $mailFor!=null?$mailFor:"");
    }
    /*end*/

    /*Send mail to admin when cron successfully run*/
    public function sendEmailNotification($cron_name, $cron_id, $remain_daily_quota)
    {
        $email_notification_enable = $this->scopeConfig->getValue('translator/general/cron_mail_send');
        if ($this->scopeConfig->getValue(self::XML_PATH_ENABLED) && $email_notification_enable) {
            $templateId = $this->scopeConfig->getValue('translator/general/cron_mail_template');
            $subject = __("Mass Product Translation Cron successfully run");
            $identity = $this->scopeConfig->getValue('customer/create_account/email_identity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $admin_mail = $this->scopeConfig->getValue('translator/general/cron_mail_id');
            $emails = explode(",", $admin_mail);
            foreach ($emails as $email) {
                $_email = explode("@", $email);
                $username = ucfirst($_email[0]);
                $vars = [
                    'date_created' => date("M d,Y"),
                    'subject' => $subject,
                    'admin_name' => $username,
                    'cron_id' => $cron_id,
                    'cron_name' => $cron_name,
                    'remain_daily_quota' => $remain_daily_quota
                ];
                $storeid = $this->_storeManager->getStore()->getStoreId();
                $transport = $this->_transportBuilder
                        ->setTemplateIdentifier($templateId)
                        ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeid])
                        ->setTemplateVars($vars)
                        ->setFrom($identity)
                        ->addTo($email, $username)
                        ->getTransport();
                $transport->sendMessage();
            }
        }
    }
}
