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
 * @package    Bss_DynamicCategory
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Bss\DynamicCategory\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    public const XML_ENABLE = 'dynamic_category/general/enable';
    public const XML_AUTO_REINDEX_PRODUCT = 'dynamic_category/general/auto_reindex_product';
    public const XML_REINDEX_PRODUCT_TIME = 'dynamic_category/general/reindex_product_time';
    public const XML_REINDEX_LOGGING = 'dynamic_category/general/reindex_logging';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get config admin depend on xml path
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is module enable
     *
     * @param int $storeId
     * @return mixed
     */
    public function isEnable($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is enable auto reindex product matching
     *
     * @param int $storeId
     * @return mixed
     */
    public function isAutoReindexProduct($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_AUTO_REINDEX_PRODUCT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get time reindex product matching every ? (hours)
     *
     * @param int $storeId
     * @return mixed
     */
    public function getReindexProductTime($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_REINDEX_PRODUCT_TIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is enable reindex logging
     *
     * @param int $storeId
     * @return mixed
     */
    public function isEnableReindexLogging($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_REINDEX_LOGGING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
