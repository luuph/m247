<?php

namespace MagePsycho\RegionCityPro\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use MagePsycho\RegionCityPro\Model\Config;
use MagePsycho\RegionCityPro\Logger\Logger;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Data extends AbstractHelper
{
    

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Url
     */
    protected $urlHelper;

    private $mode;
    private $temp;

    public function __construct(
        Context $context,
        Config $config,
        StoreManagerInterface $storeManager,
        Url $urlHelper
    ) {
        $this->config                  = $config;
        $this->storeManager            = $storeManager;
        $this->urlHelper               = $urlHelper;
        parent::__construct($context);
        $this->_initialize();
    }

    protected function _initialize()
    {
        $field = $this->decodeBase64('ZG9tYWluX3R5cGU=');
        if ($this->config->getConfigValue('magepsycho_regioncitypro/general/' . $field) == 1) {
            $key        = $this->decodeBase64('cHJvZF9saWNlbnNl');
            $this->mode = $this->decodeBase64('cHJvZHVjdGlvbg==');
        } else {
            $key        = $this->decodeBase64('ZGV2X2xpY2Vuc2U=');
            $this->mode = $this->decodeBase64('ZGV2ZWxvcG1lbnQ=');
        }
        $this->temp = $this->config->getConfigValue('magepsycho_regioncitypro/general/' . $key);
    }

    public function getMessage()
    {
        $message = $this->decodeBase64(
            'WW91IGFyZSB1c2luZyBhbiB1bmxpY2Vuc2VkIHZlcnNpb24gb2YgdGhlICdSZWdpb24gJiBDaXR5IE1hbmFnZXInIEV4dGVuc2lvbiBmb3IgZG9tYWluOiB7e0RPTUFJTn19LiBQbGVhc2UgZW50ZXIgYSB2YWxpZCBMaWNlbnNlIEtleSBmcm9tIFNUT1JFUyDCuyBDb25maWd1cmF0aW9uIMK7IE1BR0VQU1lDSE8gwrsgUmVnaW9uICYgQ2l0eSBNYW5hZ2VyIMK7IExpY2Vuc2UgS2V5LiBJZiB5b3UgZG9uJ3QgaGF2ZSBvbmUsIHBsZWFzZSBidXkgYSB2YWxpZCBsaWNlbnNlIGZyb20gPGEgaHJlZj0iaHR0cHM6Ly93d3cubWFnZXBzeWNoby5jb20vbWFnZW50bzItcmVnaW9uLWNpdHktZHJvcGRvd24tbWFuYWdlci5odG1sIiB0YXJnZXQ9Il9ibGFuayI+d3d3Lm1hZ2Vwc3ljaG8uY29tPC9hPiBvciB5b3UgY2FuIGRpcmVjdGx5IGVtYWlsIDxhIGhyZWY9Im1haWx0bzpzdXBwb3J0QG1hZ2Vwc3ljaG8uY29tIj5zdXBwb3J0QG1hZ2Vwc3ljaG8uY29tPC9hPi4='
        );
        return str_replace('{{DOMAIN}}', $this->getDomain(), $message);
    }

    public function getDomain()
    {
        $domain = $this->_urlBuilder->getBaseUrl();
        return $this->urlHelper->getBaseDomain($domain);
    }

    public function checkEntry($domain, $serial)
    {
        $salt = sha1($this->decodeBase64('bTItcmVnaW9uY2l0eXBybw=='));
        $serial = strtolower(str_replace('-', '', (string) $serial));
        if (sha1($salt . $domain . $this->mode) == $serial) {
            return true;
        }

        return false;
    }

    public function getDomainFromSystemConfig()
    {
        $websiteCode = $this->_getRequest()->getParam('website');
        $storeCode   = $this->_getRequest()->getParam('store');
        $xmlPath     = 'web/unsecure/base_url';
        if (!empty($storeCode)) {
            $domain = $this->scopeConfig->getValue(
                $xmlPath,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeCode
            );
        } elseif (!empty($websiteCode)) {
            $domain = $this->scopeConfig->getValue(
                $xmlPath,
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $websiteCode
            );
        } else {
            $domain = $this->scopeConfig->getValue(
                $xmlPath
            );
        }
        return $domain;
    }

    private function decodeBase64($text)
    {
        return base64_decode($text);
    }

    public function isValid()
    {
        $temp = $this->temp;
        if (!$this->checkEntry($this->getDomain(), $temp)) {
            return false;
        }

        return true;
    }

    public function isFxnSkipped()
    {
        if (($this->isActive() && !$this->isValid()) || !$this->isActive()) {
            return true;
        }
        return false;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_WEB,
            true
        );
    }

    public function isActive()
    {
        return $this->config->isActive();
    }

    /**
     * Logging Utility
     *
     * @param $message
     * @param bool|false $useSeparator
     * @param bool|false $force
     */
    public function log($message, $useSeparator = false, $force = false)
    {
        if ($force
            || ($this->config->isActive() && $this->config->isDebugEnabled())
        ) {
            if ($useSeparator) {
                $this->moduleLogger->customLog(str_repeat('=', 100));
            }

            $this->moduleLogger->customLog($message);
        }
    }
}
