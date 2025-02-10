<?php
/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 * @package MageCloud_BaseSeoChanges
 */
namespace MageCloud\BaseSeoChanges\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Page\Config as PageConfig;
use MageCloud\BaseSeoChanges\Helper\Data as HelperData;

/**
 * Class LayoutGenerateBlocksAfterBaseSeoChangesObserver
 * @package MageCloud\BaseSeoChanges\Observer
 */
class LayoutGenerateBlocksAfterBaseSeoChangesObserver implements ObserverInterface
{
    /**#@+
     * Robots strategy
     */
    const ROBOTS_STRATEGY_NOINDEX_NOFOLLOW = 'NOINDEX,NOFOLLOW';
    const ROBOTS_STRATEGY_NOINDEX_FOLLOW = 'NOINDEX,FOLLOW';
    const ROBOTS_STRATEGY_INDEX_FOLLOW = 'INDEX,FOLLOW';
    /**#@-*/

    /**
     * @var Request
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var PageConfig
     */
    private $pageConfig;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * LayoutGenerateBlocksAfterBaseSeoChangesObserver constructor.
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param PageConfig $pageConfig
     * @param HelperData $helperData
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        PageConfig $pageConfig,
        HelperData $helperData
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->pageConfig = $pageConfig;
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }

        $query = $this->request->getQueryValue();
        $pathInfo = $this->request->getOriginalPathInfo();

        $this->processRobotsStrategy($query, $pathInfo);

//        if (!empty($query)) {
        $this->processCanonical();
//        }

        return $this;
    }

    /**
     * Process robots strategy for specific type of urls
     *  - with dynamic variable(s)
     *  - non seo friendly product urls
     *  - non seo friendly category urls
     *  - search results urls
     *  - customer url
     *  - amp urls
     *  - review product list urls
     *
     * @param $query
     * @param $pathInfo
     * @return $this
     */
    private function processRobotsStrategy($query, $pathInfo)
    {
        if (
            !empty($query)
            || preg_match('/^\/catalogsearch/', $pathInfo)
            || preg_match('/^\/catalog\/category/', $pathInfo)
            || preg_match('/^\/catalog\/product/', $pathInfo)
            || preg_match('/^\/customer/', $pathInfo)
            || preg_match('/^\/review\/product\/list/', $pathInfo)
        ) {
            // set noindex, nofollow by default
            $this->pageConfig->setRobots(self::ROBOTS_STRATEGY_NOINDEX_NOFOLLOW);
            // if query has parameter 'p' and it's only one parameter, then robots must be set to noindex, follow
            if (array_key_exists('p', $query) && (count($query) == 1)) {
                $this->pageConfig->setRobots(self::ROBOTS_STRATEGY_NOINDEX_FOLLOW);
            } else if (array_key_exists('amp', $query)) {
                // index, follow for AMP pages
                $this->pageConfig->setRobots(self::ROBOTS_STRATEGY_INDEX_FOLLOW);
            }
        }

        return $this;
    }

    /**
     * Try to set correct canonical url for urls with query parameters
     *
     * @return $this
     */
    private function processCanonical()
    {
        $currentUrl = $this->getCurrentUrl();
        $canonicalUrl = $this->getCleanCanonicalUrl($currentUrl);

        /** @var \Magento\Framework\View\Asset\GroupedCollection $assetCollection */
        $assetCollection = $this->getAssetCollection();
        if (!$assetCollection) {
            return $this;
        }

        $canonicalGroup = $assetCollection->getGroupByContentType('canonical');
        if (!$canonicalGroup) {
            $this->addCanonicalUrl($canonicalUrl);
        } else if ($canonicalGroup->has($currentUrl) && ($currentUrl != $canonicalUrl)) {
            $canonicalGroup->remove($currentUrl);
            $this->addCanonicalUrl($canonicalUrl);
        }

        return $this;
    }

    /**
     * @param $canonicalUrl
     * @return $this
     */
    private function addCanonicalUrl($canonicalUrl)
    {
        $this->pageConfig->addRemotePageAsset(
            $canonicalUrl,
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );

        return $this;
    }

    /**
     * @return GroupedCollection
     */
    private function getAssetCollection()
    {
        return $this->pageConfig->getAssetCollection();
    }

    /**
     * @return string
     */
    private function getCurrentUrl()
    {
        return $this->urlBuilder->getCurrentUrl();
    }

    /**
     * @param $url
     * @return mixed
     */
    private function getCleanCanonicalUrl($url)
    {
        return preg_replace('/\?.*/', '', $url);
    }
}