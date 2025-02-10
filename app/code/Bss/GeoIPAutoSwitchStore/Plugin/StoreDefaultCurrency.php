<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_GeoIPAutoSwitchStore
 * @author    Extension Team
 * @copyright Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GeoIPAutoSwitchStore\Plugin;

use Bss\GeoIPAutoSwitchStore\Helper\Config as GeoConfig;
use Bss\GeoIPAutoSwitchStore\Helper\Data as GeoDataHelper;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class StoreDefaultCurrency
{
    /**
     * @var GeoConfig
     */
    protected $geoConfig;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * Session entity
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GeoDataHelper
     */
    protected $geoDataHelper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * StoreDefaultCurrency constructor.
     * @param GeoConfig $geoConfig
     * @param HttpContext $httpContext
     * @param SessionManagerInterface $sessionManager
     * @param StoreManagerInterface $storeManager
     * @param GeoDataHelper $geoDataHelper
     * @param RequestInterface $request
     */
    public function __construct(
        GeoConfig $geoConfig,
        HttpContext $httpContext,
        SessionManagerInterface $sessionManager,
        StoreManagerInterface $storeManager,
        GeoDataHelper $geoDataHelper,
        RequestInterface $request
    ) {
        $this->geoConfig = $geoConfig;
        $this->httpContext = $httpContext;
        $this->_session = $sessionManager;
        $this->storeManager = $storeManager;
        $this->geoDataHelper = $geoDataHelper;
        $this->request = $request;
    }

    /**
     * @param Store $store
     * @param $currencyCode
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetCurrentCurrencyCode(
        \Magento\Store\Model\Store $store,
        $currencyCode
    ) {
        if (!$this->geoConfig->isEnabled() ||
            !$this->geoConfig->isEnabledCurrency()) {
            return $currencyCode;
        }

        $availableCurrencyCodes = array_values($store->getAvailableCurrencyCodes(false));
        // try to get currently set code among allowed
        $codeContext = $this->httpContext->getValue(HttpContext::CONTEXT_CURRENCY);
        $code = $codeContext ?? $this->_getSession($store)->getCurrencyCode();
        if (empty($code) || !\in_array($code, $availableCurrencyCodes)) {
            $code = $store->getDefaultCurrencyCode();
            if (!\in_array($code, $availableCurrencyCodes) && !empty($availableCurrencyCodes)) {
                $code = $availableCurrencyCodes[0];
            }
            $this->geoConfig->setCustomerIp($this->geoDataHelper->getIpCustomer($this->request->getParam('ipTester')));
            $this->geoConfig->geoIPDebug("SET CURRENCY>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");
            $this->setStoreCurrencySession($store, $code);
            $this->geoConfig->geoIPDebug("END SET CURRENCY<<<<<<<<<<<<<<<<<<<<<<<<<<<");
        }

        return $code;
    }

    /**
     * @param Store $store
     * @return SessionManagerInterface
     */
    protected function _getSession($store)
    {
        if (!$this->_session->isSessionExists()) {
            $this->_session->setName('store_' . $store->getCode());
            $this->_session->start();
        }
        return $this->_session;
    }

    /**
     * @param Store $store
     * @param string $code
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function setStoreCurrencySession($store, $code)
    {
        $this->_getSession($store)->setCurrencyCode($code);
        $defaultCode = ($this->storeManager->getStore() !== null)
            ? $this->storeManager->getStore()->getDefaultCurrency()->getCode()
            : $this->storeManager->getWebsite()->getDefaultStore()->getDefaultCurrency()->getCode();

        if ($code) {
            $this->geoConfig->geoIPDebug("Set currency $code for store id {$this->storeManager->getStore()->getId()}");
        } else {
            $this->geoConfig->geoIPDebug("Set default currency $defaultCode for store id {$this->storeManager->getStore()->getId()}");
        }
        $this->httpContext->setValue(HttpContext::CONTEXT_CURRENCY, $code, $defaultCode);
    }
}
