<?php

namespace MagePsycho\RegionCityPro\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Config implements ConfigInterface
{
    private const XML_PATH_ENABLED = 'magepsycho_regioncitypro/general/enabled';
    private const XML_PATH_DEBUG = 'magepsycho_regioncitypro/general/debug';

    private const XML_PATH_COUNTRY_SEARCHABLE = 'magepsycho_regioncitypro/country/searchable';
    private const XML_PATH_REGION_SEARCHABLE = 'magepsycho_regioncitypro/region/searchable';
    private const XML_PATH_CITY_SEARCHABLE = 'magepsycho_regioncitypro/city/searchable';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function getConfigFlag($xmlPath, $storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            $xmlPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function getConfigValue($xmlPath, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $xmlPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isEnabled($storeId = null)
    {
        return $this->getConfigFlag(self::XML_PATH_ENABLED, $storeId);
    }

    public function isActive($storeId = null)
    {
        return $this->isEnabled($storeId);
    }

    public function isDebugEnabled($storeId = null)
    {
        return $this->getConfigFlag(self::XML_PATH_DEBUG, $storeId);
    }

    public function isCitySearchable($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_CITY_SEARCHABLE, $storeId);
    }

    public function isCountrySearchable($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_COUNTRY_SEARCHABLE, $storeId);
    }

    public function isRegionSearchable($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_REGION_SEARCHABLE, $storeId);
    }
}
