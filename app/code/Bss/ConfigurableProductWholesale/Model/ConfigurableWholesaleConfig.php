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
 * @package   Bss_ConfigurableProductWholesale
 * @author    Extension Team
 * @copyright Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableProductWholesale\Model;

use Bss\ConfigurableProductWholesale\Api\ConfigurableWholesaleConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class ConfigurableWholesaleConfig implements ConfigurableWholesaleConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $storeId
     * @return array
     * @throws LocalizedException
     */
    public function getConfig($storeId)
    {
        $data = [];
        try {
            if ($storeId || $storeId == 0) {
                $generalPaths = [
                    'active',
                    'stock_number',
                    'tier_price_advanced',
                    'range_price',
                    'ajax_load',
                    'sorting',
                ];

                $multiselectPaths = [
                    'show_attr',
                    'hide_price',
                    'active_customer_groups'
                ];

                $designPaths = [
                    'header_background_color',
                    'header_text_color'
                ];

                $displayPaths = [
                    'mobile_active',
                    'tab_active'
                ];

                $configuration = [];

                foreach ($generalPaths as $path) {
                    $configuration['general'][$path] = $this->getGeneralConfigByPath($path, $storeId);
                }

                foreach ($multiselectPaths as $multiselect) {
                    $values = $this->getMultiSelectConfigByPath($multiselect, $storeId);
                    if (is_array($values)) {
                        foreach ($values as $value) {
                            $configuration['general'][$multiselect][] = $value;
                        }
                    } else {
                        $configuration['general'][$multiselect] = [];
                    }
                }

                foreach ($designPaths as $designPath) {
                    $configuration['design'][$designPath] = $this->getDesignConfigByPath($designPath, $storeId);
                }

                foreach ($displayPaths as $displayPath) {
                    $configuration['display'][$displayPath] = $this->getDisplayConfigByPath($displayPath, $storeId);
                    if ($displayPath == 'mobile_active') {
                        $configuration['display']['mobile_attr'] = $this->getDisplayAttributes($displayPath, $storeId);
                    }
                    if ($displayPath == 'tab_active') {
                        $configuration['display']['tab_attr'] = $this->getDisplayAttributes($displayPath, $storeId);
                    }
                }
                $data[] = $configuration;
                return $data;
            }
        } catch (\Exception $exception) {
            throw new  LocalizedException(__($exception->getMessage()));
        }
        return [];
    }

    /**
     * @param $path
     * @param $storeId
     * @return mixed
     */
    private function getGeneralConfigByPath($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            'configurableproductwholesale/general/'.$path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Multi Select Config By Path
     *
     * @param string $path
     * @param string $storeId
     * @return false|string[]|void
     */
    private function getMultiSelectConfigByPath($path, $storeId = null)
    {
        $active = $this->getGeneralConfigByPath('active', $storeId);
        if ($active) {
            $result = $this->scopeConfig->getValue(
                'configurableproductwholesale/general/' . $path,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            return explode(',', $result);
        }
    }

    /**
     * @param $path
     * @param $storeId
     * @return mixed
     */
    private function getDesignConfigByPath($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            'configurableproductwholesale/design/' . $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $path
     * @param $storeId
     * @return mixed
     */
    private function getDisplayConfigByPath($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            'configurableproductwholesale/display/' . $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $path
     * @param null $storeId
     * @return array|string[]
     */
    private function getDisplayAttributes($path, $storeId = null)
    {
        $config = [];
        $active = $this->getDisplayConfigByPath($path, $storeId);
        if ($active) {
            if ($path == 'mobile_active') {
                $config = explode(',', $this->getDisplayConfigByPath('mobile_attr', $storeId));
            }
            if ($path == 'tab_active') {
                $config = explode(',', $this->getDisplayConfigByPath('tab_attr', $storeId));
            }
        }
        return $config;
    }
}
