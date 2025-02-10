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
 * @category   BSS
 * @package    Bss_GeoIPAutoSwitchStore
 * @author     Extension Team
 * @copyright  Copyright (c) 2016-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GeoIPAutoSwitchStore\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Bss\GeoIPAutoSwitchStore\Cookie\GeoSession;
use Bss\GeoIPAutoSwitchStore\Cookie\GeoIp as GeoCookie;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class GeoIpSwitcher
{
    /**
     * @var \Bss\GeoIPAutoSwitchStore\Helper\Data
     */
    private $dataHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Bss\GeoIPAutoSwitchStore\Helper\GeoIPData
     */
    private $geoIpHelper;

    /**
     * @var \Bss\GeoIPAutoSwitchStore\Helper\Config
     */
    private $geoIpConfig;

    /**
     * @var GeoSession
     */
    private $geoSession;

    /**
     * @var GeoCookie
     */
    private $geoCookie;

    /**
     * @var \Bss\GeoIPAutoSwitchStore\Model\Validation\SkipRedirectInterface
     */
    protected $skipRedirect;

    /**
     * @var \Bss\GeoIPAutoSwitchStore\Model\Validation\BlackListInterface
     */
    protected $blackList;

    /**
     * @var \Bss\GeoIPAutoSwitchStore\Model\Validation\DefaultRedirectInterface
     */
    protected $defaultRedirect;

    /**
     * @var int
     */
    protected $currentStoreId = 0;

    /**
     * @var null|Store|StoreInterface
     */
    private $currentStoreObject;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreSwitcher
     */
    protected $storeSwitcher;

    /**
     * SwitchStore constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Bss\GeoIPAutoSwitchStore\Helper\Data $dataHelper
     * @param \Bss\GeoIPAutoSwitchStore\Helper\Config $geoIpConfig
     * @param \Bss\GeoIPAutoSwitchStore\Helper\GeoIPData $geoIpHelper
     * @param GeoSession $geoSession
     * @param GeoCookie $geoCookie
     * @param \Bss\GeoIPAutoSwitchStore\Model\Validation\SkipRedirectInterface $skipRedirect
     * @param \Bss\GeoIPAutoSwitchStore\Model\Validation\BlackListInterface $blackList
     * @param \Bss\GeoIPAutoSwitchStore\Model\Validation\DefaultRedirectInterface $defaultRedirect
     * @param \Magento\Framework\App\RequestInterface $request
     * @param StoreSwitcher $storeSwitcher
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Bss\GeoIPAutoSwitchStore\Helper\Data $dataHelper,
        \Bss\GeoIPAutoSwitchStore\Helper\Config $geoIpConfig,
        \Bss\GeoIPAutoSwitchStore\Helper\GeoIPData $geoIpHelper,
        GeoSession $geoSession,
        GeoCookie $geoCookie,
        \Bss\GeoIPAutoSwitchStore\Model\Validation\SkipRedirectInterface $skipRedirect,
        \Bss\GeoIPAutoSwitchStore\Model\Validation\BlackListInterface $blackList,
        \Bss\GeoIPAutoSwitchStore\Model\Validation\DefaultRedirectInterface $defaultRedirect,
        \Magento\Framework\App\RequestInterface $request,
        \Bss\GeoIPAutoSwitchStore\Plugin\StoreSwitcher $storeSwitcher
    ) {
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->dataHelper = $dataHelper;
        $this->geoIpHelper = $geoIpHelper;
        $this->geoSession = $geoSession;
        $this->geoCookie = $geoCookie;
        $this->geoIpConfig = $geoIpConfig;
        $this->skipRedirect = $skipRedirect;
        $this->blackList = $blackList;
        $this->defaultRedirect = $defaultRedirect;
        $this->request = $request;
        $this->storeSwitcher = $storeSwitcher;
    }

    /**
     * @param \Magento\Framework\App\Response\Http $subject
     * @param string $requestUri
     */
    public function setRedirect($subject, $requestUri)
    {
        // This cookie tell us that customer has visited/redirected
        // So, we can use this as flag to check if customer has any redirection to predict next action
        $this->geoSession->setSession(GeoSession::COOKIE_CUSTOMER_HAS_REDIRECTED, 1);
        $subject->setNoCacheHeaders();
        $subject->setRedirect($requestUri);
        $this->geoIpConfig->geoIPDebug("Redirect to url, url: $requestUri");
    }

    /**
     * @param \Magento\Framework\App\Response\Http $subject
     * @return $this|bool|void|array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     * @throws \Magento\Store\Model\StoreSwitcher\CannotSwitchStoreException
     */
    public function beforeSendResponse(
        \Magento\Framework\App\Response\Http $subject
    ) {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $this->request;
        if (!$this->geoIpConfig->isEnabled() ||
            $request->isXmlHttpRequest()) {
            return [];
        }

        $this->getCurrentStoreObject();
        $testerIp = $request->getParam('ipTester');
        $httpUserAgent = $this->dataHelper->returnHttpUserAgent();
        $customerIp = $this->dataHelper->getIpCustomer($testerIp); // IP
        $countryCode = $this->getCountryCode($testerIp, $customerIp); // IP lookup

        // Validate black list ------------------
        // If customer is in black list
        // Then do redirect to black list url
        $blackListUrl = $this->getBlackListUrl();
        $currentUrl = $this->urlBuilder->getCurrentUrl(); // URL
        $this->geoIpConfig->setCustomerIp($customerIp);
        $this->geoIpConfig->setCurrentUrl($currentUrl);
        $this->geoIpConfig->geoIPDebug("START SWITCH STORE>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");
        $this->geoIpConfig->geoIPDebug("IP: $customerIp");
        $this->geoIpConfig->geoIPDebug("IP lookup: $countryCode");
        $this->geoIpConfig->geoIPDebug("URL visit: $currentUrl");
        $this->geoIpConfig->geoIPDebug("Scope redirect: {$this->geoIpConfig->getRedirectScope()}");

        if (($this->blackList->setIp($customerIp)->validate(1) ||
                $this->blackList->setCountries($countryCode)->validate(2)) &&
            $blackListUrl) {
            if (trim($blackListUrl) !== trim($currentUrl)) {
                $this->geoSession->setSession(GeoSession::COOKIE_LAST_STORE_ID_VISITED, $this->currentStoreId);
                $this->setRedirect($subject, $blackListUrl);
            } else {
                $this->geoIpConfig->geoIPDebug("Current url is in blacklist, url: " . $currentUrl);
            }
            $this->geoIpConfig->geoIPDebug("END SWITCH STORE<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
            return [];
        }
        // End validate black list ------------------

        // Check skip redirect -------------------
        // If visitor is bot or he visit skip url
        // Then do nothing
        if ($this->skipRedirect->setIp($customerIp)->validate(1) ||
            $this->skipRedirect->setHttpAgent($httpUserAgent)->validate(3) > 0 ||
            !$countryCode) {
            $this->geoIpConfig->geoIPDebug("Ip is in skip redirect, ip: " . $customerIp);
            $this->geoIpConfig->geoIPDebug("END SWITCH STORE<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
            return [];
        }
        $currentPath = $request->getOriginalPathInfo();
        $restrictionUrl = $this->skipRedirect->setUrl($currentPath)->validate(2);
        if ($restrictionUrl) {
            $this->geoIpConfig->geoIPDebug("Current url is in skip redirect, url: " . $currentUrl);
            $this->geoIpConfig->geoIPDebug("END SWITCH STORE<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
            return [];
        }
        // End check skip -------------------

        // Default redirect --------------
        $this->defaultRedirect->setUrl($currentUrl);
        if ($this->defaultRedirect->validate()) {
            $destinationStore = $this->getDestinationStore($countryCode, $this->currentStoreId);
            if (is_bool($destinationStore) || !$destinationStore) {
                $this->geoIpConfig->geoIPDebug("Current url is default redirect, url: " . $currentUrl);
                $this->geoIpConfig->geoIPDebug("END SWITCH STORE<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
                return [];
            }
            if ($this->isStoreObject($destinationStore)) {
                $this->geoIpConfig->geoIPDebug("Current url is not default redirect, store id: {$destinationStore->getId()}");
                $this->geoSession->setSession(GeoSession::COOKIE_LAST_STORE_ID_VISITED, $destinationStore->getId());
                $urlDefaultRedirect = $this->storeSwitcher->getUrlRedirect(
                    $this->currentStoreObject,
                    $destinationStore,
                    $request
                );
                $this->setRedirect($subject, $urlDefaultRedirect);

                $this->geoIpConfig->geoIPDebug("END SWITCH STORE<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
                return [];
            }
        }

        // Check and redirect to prefer store
        $this->switchStore(
            $customerIp,
            $this->geoIpConfig->isEnabledCookie(),
            $countryCode,
            $request,
            $subject
        );
        $this->geoIpConfig->geoIPDebug("END SWITCH STORE<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
        return [];
        // End redirect
    }

    /**
     * @param $customerIp
     * @param $enableCookie
     * @param $countryCode
     * @param $request
     * @param \Magento\Framework\App\Response\Http $subject
     * @return bool|void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     * @throws \Magento\Store\Model\StoreSwitcher\CannotSwitchStoreException
     */
    protected function switchStore(
        $customerIp,
        $enableCookie,
        $countryCode,
        $request,
        $subject
    ) {
        $this->geoSession->setSession(GeoSession::COOKIE_COUNTRY, $countryCode);

        // 1. If customer visit store by popup or store switcher or currency switcher or website switcher
        // Just need set last store he visited and do nothing
        if ($this->isRedirectFromStoreSwitch($request)) {
            $storeViewObject = $this->getStoreCodeFromStoreSwitch($request);
            if ($storeViewObject) {
                $this->geoCookie->setCookie(GeoCookie::COOKIE_LAST_STORE_ID_VISITED, $storeViewObject->getId());
                $this->geoSession->setSession(GeoSession::COOKIE_CUSTOMER_HAS_REDIRECTED, 1);
                $this->geoIpConfig->geoIPDebug("Customer redirect from switch store, store id {$storeViewObject->getId()}");
                return;
            }
        }

        // Switch by currency switcher
        if ($this->isRedirectFromCurrencySwitch($request)) {
            $this->geoCookie->setCookie(GeoCookie::COOKIE_LAST_STORE_ID_VISITED, $this->currentStoreId);
            $this->geoSession->setSession(GeoSession::COOKIE_CUSTOMER_HAS_REDIRECTED, 1);
            $this->geoIpConfig->geoIPDebug("Customer redirect from switch currency");
            return;
        }

        // Switch by website switcher
        if ($this->isRedirectFromWebsiteSwitch($request)) {
            $this->geoIpConfig->geoIPDebug("Customer redirect from switch website");
            $url = strtok($this->urlBuilder->getCurrentUrl(), '?');
            $this->setRedirect($subject, $url);
            $this->geoCookie->setCookie(GeoSession::COOKIE_CUSTOMER_HAS_SWITCH_BY_WEBSITE_SWITCHER, 1, 15);
            $this->geoSession->setSession(GeoSession::COOKIE_CUSTOMER_HAS_REDIRECTED, 1);
            return;
        }
        if ($this->geoCookie->getCookie(GeoSession::COOKIE_CUSTOMER_HAS_SWITCH_BY_WEBSITE_SWITCHER)) {
            $this->geoCookie->setCookie(GeoCookie::COOKIE_LAST_STORE_ID_VISITED, $this->currentStoreId);
            $this->geoIpConfig->geoIPDebug("Customer has cookie switch_by_website_switcher");
            return;
        }

        $lastStoreVisited = $this->geoCookie->getCookie(GeoSession::COOKIE_LAST_STORE_ID_VISITED);
        // if 1 passed
        // 2. If customer visit destination store which assigned for his country
        // Stop redirect and do nothing
        if ($this->didCustomerVisitDestinationStore($countryCode)) {
            $this->geoSession->setSession(GeoSession::COOKIE_CUSTOMER_HAS_REDIRECTED, 1);
            if (!$lastStoreVisited) {
                $this->geoCookie->setCookie(GeoCookie::COOKIE_LAST_STORE_ID_VISITED, $this->currentStoreId);
                $this->geoIpConfig->geoIPDebug("Customer visit destination store, store id: $this->currentStoreId");
                return;
            }
        }

        // if 1, 2 passed
        // 3. If customer has visited store before and their request are neither store switcher,
        // currency switcher nor website switcher
        // then do redirect to prev store he visited
        // If not then next to 3.
        if ($lastStoreVisited) {
            $this->geoSession->setSession(GeoSession::COOKIE_CUSTOMER_HAS_REDIRECTED, 1);
            $backToStatus = $this->redirectBackToPrevStore($lastStoreVisited, $this->currentStoreId);
            if ($backToStatus == 'back') {
                $this->geoIpConfig->geoIPDebug("Redirect to last store visited, store id: " . $lastStoreVisited);
                $this->geoCookie->setCookie(GeoCookie::COOKIE_LAST_STORE_ID_VISITED, $lastStoreVisited);
                $lastStoreVisited = $this->storeManager->getStore($lastStoreVisited);
                $urlToBack = $this->storeSwitcher->getUrlRedirect(
                    $this->currentStoreObject,
                    $lastStoreVisited,
                    $request
                );
                if (!$this->didCustomerVisitDestinationStore($countryCode)) {
                    $this->setRedirect($subject, $urlToBack);
                }
                return;
            } elseif ($backToStatus == 'break') {
                $this->geoIpConfig->geoIPDebug("Current store is last store visited, store id: " . $lastStoreVisited);
                return;
            }
        }

        // 4. Last part
        // Redirect customer to proper store view which assigned to his country
        if (!$this->handleByPopup($countryCode, $request)) {
            $destinationStore = $this->getDestinationStore($countryCode, $this->currentStoreId);
            if ($destinationStore === true) {
                $this->geoIpConfig->geoIPDebug("Redirect to current store, store id: " . $this->currentStoreId);
                return;
            }
            if ($this->isStoreObject($destinationStore)) {
                $this->geoIpConfig->geoIPDebug("Redirect to other store, store id: {$destinationStore->getId()}");
                $this->geoCookie->setCookie(GeoCookie::COOKIE_LAST_STORE_ID_VISITED, $destinationStore->getId());
                $urlSwitch = $this->storeSwitcher->getUrlRedirect(
                    $this->currentStoreObject,
                    $destinationStore,
                    $request
                );
                $this->setRedirect($subject, $urlSwitch);
                return;
            }
            if (!$destinationStore) {
                $this->geoIpConfig->geoIPDebug("Redirect to current store, store id: " . $this->currentStoreId);
                return;
            }
        }
    }

    /**
     * @param int $lastStoreVisited
     * @param int $currentStoreId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Store\Model\StoreSwitcher\CannotSwitchStoreException
     */
    protected function redirectBackToPrevStore(
        $lastStoreVisited,
        $currentStoreId
    ) {
        if ($lastStoreVisited == $currentStoreId) {
            return 'break';
        }
        if ($this->storeManager->getStore($lastStoreVisited)) {
            return 'back';
        }
        return 'continue';
    }

    /**
     * @param string $countryCode
     * @param string|int $currentStoreId
     * @param string|null $customerIp
     * @return bool|null|StoreInterface|Store
     */
    protected function getDestinationStore($countryCode, $currentStoreId)
    {
        $storeFirstVisit = null;
        $storeVisit = null;
        $listStores = $this->getListingStoreFromScope();
        $this->geoIpConfig->geoIPDebug("Checking default redirect config, current store id: $currentStoreId");

        foreach ($listStores as $store) {
            $countryStore = $this->geoIpConfig->getCountries($store->getId());
            if ($countryStore !== null && strpos($countryStore, $countryCode) !== false && $store->isActive()) {
                $this->geoIpConfig->geoIPDebug("Country config [$countryStore] match with [$countryCode]");
                if (!$storeFirstVisit) {
                    $storeFirstVisit = $store;
                }
                if ($store->getId() == $currentStoreId) {
                    $storeVisit = $store;
                    // Customer visit to store which has assigned to his country, so we dont need do anymore
                    return true;
                }
            } else {
                $this->geoIpConfig->geoIPDebug("Country config [$countryStore] not match with [$countryCode]");
            }
        }
        return $storeFirstVisit;
    }

    /**
     * @param string $countryCode
     * @param RequestInterface $request
     * @return bool
     */
    protected function handleByPopup($countryCode, $request)
    {
        if ($this->geoIpConfig->isEnabledPopup()) {
            $this->geoIpConfig->geoIPDebug("Admin config enable popup, skip auto redirect");
            return true;
        }
        if ($request->getParam('is_from_popup')) {
            $this->geoIpConfig->geoIPDebug("Request from popup, skip auto redirect");
            return true;
        }
        if ($this->geoSession->getSession(GeoSession::COOKIE_CUSTOMER_HAS_OPEN_POPUP)) {
            $this->geoIpConfig->geoIPDebug("Request session has open popup, skip auto redirect");
            return $this->geoSession->getSession(GeoSession::COOKIE_CUSTOMER_HAS_OPEN_POPUP);
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function didCustomerVisitedBefore()
    {
        // If customer has visited website before
        // Then redirect him to last store that he viewed
        $lastStoreVisited = $this->geoCookie->getCookie(GeoCookie::COOKIE_LAST_STORE_ID_VISITED);
        return $lastStoreVisited;
    }

    /**
     * @param string $countryCode
     * @return bool
     */
    private function didCustomerVisitDestinationStore($countryCode)
    {
        // If customer visit store which assigned for his country (in case: Add Store Code to Urls = Yes )
        $destinationStore = $this->getStoreByCountryCode($countryCode);
        if ($destinationStore &&
            $destinationStore->getId() == $this->currentStoreObject->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getBlackListUrl()
    {
        $blockUrl = $this->geoIpConfig->getUrlBlackList() !== null ? trim($this->geoIpConfig->getUrlBlackList()) : '';
        $currentUrl = $this->currentStoreObject->getCurrentUrl(false);

        if (stripos($blockUrl, 'http') === false) {
            $blockUrl = $this->urlBuilder->getUrl($blockUrl);
        }

        return strpos($currentUrl, $blockUrl) === false ? $blockUrl : false;
    }

    /**
     * @param string $testerIp
     * @param string $customerIp
     * @return mixed|string|null
     */
    private function getCountryCode($testerIp, $customerIp)
    {
        $countryCode = null;
        if (!$testerIp) {
            $countryCode = $this->geoSession->getSession(GeoSession::COOKIE_COUNTRY);
        }
        return $countryCode ?: $this->dataHelper->getCountryCodeFromIp($customerIp);
    }

    /**
     * @param string $key
     * @return array
     */
    private function getListingStoreFromScope($key = 'obj')
    {
        $redirectScope = $this->getRedirectScope();
        return $this->geoIpHelper->getListingStoreFromScope(
            $this->currentStoreObject,
            $redirectScope,
            $key
        );
    }

    /**
     * @param string $countryCode
     * @return bool|null|\Magento\Store\Api\Data\StoreInterface
     */
    private function getStoreByCountryCode($countryCode)
    {
        $redirectScope = $this->getRedirectScope();
        return $this->geoIpHelper->getStoreByCountryCode(
            $this->currentStoreObject,
            $countryCode,
            $redirectScope
        );
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface|StoreManagerInterface|null|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCurrentStoreObject()
    {
        if (!$this->currentStoreObject) {
            $this->currentStoreObject = $this->storeManager->getStore();
        }
        if (!$this->currentStoreId) {
            $this->currentStoreId = $this->currentStoreObject->getId();
        }
    }

    /**
     * If customer user switch store by store switcher or popup
     * then return true
     * @param RequestInterface $request
     * @return bool
     */
    private function isRedirectFromStoreSwitch($request)
    {
        // Get url from url builder, because the request var not getting params before set state area
        $originalUrl = $this->urlBuilder->getCurrentUrl();
        $originalPath = $request->getPathInfo();
        if ($request->getParam(StoreManagerInterface::PARAM_NAME) &&
            $request->getParam('___from_store') &&
            strpos($originalPath, 'stores/store/redirect') !== false ||
            strpos($originalPath, 'stores/store/switch') !== false ||
            strpos($originalUrl, 'stores/store/switch') !== false ||
            strpos($originalUrl, 'stores/store/redirect') !== false) {
            return true;
        }
        return false;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    private function isRedirectFromCurrencySwitch($request)
    {
        $originalUrl = $this->urlBuilder->getCurrentUrl();
        $originalPath = $request->getPathInfo();
        if (strpos($originalPath, 'directory/currency/switch') !== false ||
            strpos($originalUrl, 'directory/currency/switch') !== false) {
            return true;
        }
        return false;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    private function isRedirectFromWebsiteSwitch($request)
    {
        $originalUrl = $this->urlBuilder->getCurrentUrl();
        $originalPath = $request->getPathInfo();
        if ($request->getParam('___from_store') &&
            $request->getParam(\Bss\GeoIPAutoSwitchStore\Block\Switcher::IS_WEBSITE_SWITCHER) == 1) {
            return true;
        }
        return false;
    }

    /**
     * @param RequestInterface $request
     * @return Store|StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStoreCodeFromStoreSwitch($request)
    {
        $requestStoreCode = $request->getParam(StoreManagerInterface::PARAM_NAME);
        if ($requestStoreCode) {
            return $this->dataHelper->getStoreByCode($requestStoreCode);
        }
        return null;
    }

    /**
     * @return string
     */
    private function getRedirectScope()
    {
        $redirectScope = $this->geoIpConfig->getRedirectScope() ?: 'website';
        return $redirectScope;
    }

    /**
     * @param null|StoreInterface|string|int $object
     * @return bool
     */
    private function isStoreObject($object)
    {
        return $object instanceof StoreInterface || $object instanceof Store;
    }
}
