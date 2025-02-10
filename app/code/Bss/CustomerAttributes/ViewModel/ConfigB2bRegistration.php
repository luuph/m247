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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\ViewModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class ConfigB2bRegistration implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Construct
     *
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->moduleManager = $moduleManager;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check install module B2bRegistration
     *
     * @return bool
     */
    public function isInstallB2bRegistration()
    {
        return $this->moduleManager->isEnabled('Bss_B2bRegistration');
    }

    /**
     * Check enable module B2bRegistration
     *
     * @param int|string|null $storeId
     * @return bool
     * @throws LocalizedException
     */
    public function isEnableB2bRegistration($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $configEnable = (bool)$this->scopeConfig->getValue(
            'b2b/general/enable',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        if ($configEnable && $this->isInstallB2bRegistration()) {
            return true;
        }
        return false;
    }
}
