<?php

/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. * */

namespace Biztech\Translator\Observer;

use Biztech\Translator\Helper\Data;
use Magento\Config\Model\Config\Factory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class checkKey implements ObserverInterface
{
    const XML_PATH_ACTIVATION_KEY = 'translator/activation/key';
    const XML_PATH_DATA = 'translator/activation/data';

    protected $scopeConfig;
    protected $encryptr;
    protected $configFactory;
    protected $helper;
    protected $productMetadataInterface;
    protected $request;
    protected $resourceConfig;
    protected $configModel;
    protected $configValueFactory;
    protected $_cacheTypeList;
    protected $_cacheFrontendPool;
    protected $_curl;
    protected $jsonDecoder;
    protected $_date;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptr,
        Factory $configFactory,
        Data $helper,
        ProductMetadataInterface $productMetadataInterface,
        RequestInterface $request,
        Config $resourceConfig,
        ValueFactory $configValueFactory,
        \Magento\Config\Model\Config $configModel,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        Curl $curl,
        DecoderInterface $jsonDecoder,
        DateTime $datetime
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptr = $encryptr;
        $this->configFactory = $configFactory;
        $this->helper = $helper;
        $this->productMetadataInterface = $productMetadataInterface;
        $this->request = $request;
        $this->resourceConfig = $resourceConfig;
        $this->configModel = $configModel;
        $this->configValueFactory = $configValueFactory;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_curl = $curl;
        $this->jsonDecoder = $jsonDecoder;
        $this->_date = $datetime;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $params = $this->request->getParam('groups');
        if (!isset($params['activation'])) {
            return;
        }
        $k = $params['activation']['fields']['key']['value'];
        // $k = $this->scopeConfig->getValue(self::XML_PATH_ACTIVATION_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $s = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('https://www.appjetty.com/extension/licence.php'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'key=' . urlencode($k) . '&domains=' . urlencode(implode(',', $this->helper->getAllStoreDomains())) . '&sec=magento2-translator');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $content = curl_exec($ch);
        $res1 = json_decode($content);
        // $res1 = json_decode($this->getActivation($k));
        $res = (array)$res1;
        $moduleStatus = $this->resourceConfig;

        if (empty($res)) {
            $moduleStatus->saveConfig('translator/activation/key', "", 'default', 0);
            $moduleStatus->saveConfig('translator/general/is_active', "", 'default', 0);
            $data = $this->_scopeConfig('translator/activation/data', "", 'default', 0);
            $this->resourceConfig->saveConfig('translator/activation/data', $data, 'default', 0);
            $this->resourceConfig->saveConfig('translator/activation/websites', '', 'default', 0);
            $this->resourceConfig->saveConfig('translator/activation/store', '', 'default', 0);
            return;
        }
        $data = '';
        $web = '';
        $en = '';
        if (isset($res['dom']) && intval($res['c']) > 0 && intval($res['suc']) == 1) {
            $data = $this->encryptr->encrypt(base64_encode(json_encode($res1)));
            if (!$s) {
                if (isset($params['activation']['fields']['store']['value'])) {
                    $s = $params['activation']['fields']['store']['value'];
                }
            }
            $en = $res['suc'];
            if (isset($s) && $s != null) {
                $web = $this->encryptr->encrypt($data . implode(',', $s) . $data);
            } else {
                $web = $this->encryptr->encrypt($data . $data);
            }
        } else {
            $moduleStatus->saveConfig('translator/activation/key', "", 'default', 0);
            $moduleStatus->saveConfig('translator/general/is_active', 0, 'default', 0);
            $this->resourceConfig->saveConfig('translator/activation/store', '', 'default', 0);
        }
        $this->resourceConfig->saveConfig('translator/activation/data', $data, 'default', 0);
        $this->resourceConfig->saveConfig('translator/activation/websites', $web, 'default', 0);
        $this->resourceConfig->saveConfig('translator/activation/en', $en, 'default', 0);
        $this->resourceConfig->saveConfig('translator/activation/installed', 1, 'default', 0);
        
        $version = $this->productMetadataInterface->getVersion();
        if (version_compare($version, '2.1', '<')) {
            $higherversion = 0;
            $lowerversion = 1;
        } else {
            $higherversion = 1;
            $lowerversion = 0;
        }
        $this->resourceConfig->saveConfig('translator/general/magento_higher_version', $higherversion, 'default', 0);
        $this->resourceConfig->saveConfig('translator/general/magento_lower_version', $lowerversion, 'default', 0);
        
        /*manage date for newly added product consider to translate*/
        $newlyAddedProductTranslate = $this->helper->newAddedProductTranslateEnable();
        if ($newlyAddedProductTranslate) {
            if ($this->scopeConfig->getValue("translator/general/newly_added_product_date")==null) {
                $this->resourceConfig->saveConfig('translator/general/newly_added_product_date', $this->_date->gmtDate(), 'default', 0);
            }
        } else {
            $this->resourceConfig->saveConfig('translator/general/newly_added_product_date', null, 'default', 0);
        }
        /*end*/
        $types = ['config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice'];
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
