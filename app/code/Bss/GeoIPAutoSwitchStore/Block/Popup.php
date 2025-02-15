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
 * @copyright  Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GeoIPAutoSwitchStore\Block;

class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\GeoIPAutoSwitchStore\Helper\Config
     */
    protected $geoIpConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Bss\GeoIPAutoSwitchStore\Helper\Config $helper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Bss\GeoIPAutoSwitchStore\Helper\Config $geoIpConfig,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->geoIpConfig = $geoIpConfig;
        $this->storeManager = $context->getStoreManager();
        $this->request = $context->getRequest();
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPopupEnable()
    {
        return $this->geoIpConfig->isEnabled() &&
            $this->geoIpConfig->isEnabledPopup();
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('geoip/index/switchstore');
    }

    /**
     * @return string
     */
    public function getCurrentPath()
    {
        $currentPath = $this->request->getOriginalPathInfo();
        $currentPath = ltrim($currentPath, '/');

        return $currentPath;
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        $url = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $url;
    }
}
