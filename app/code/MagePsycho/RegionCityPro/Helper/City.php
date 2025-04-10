<?php

namespace MagePsycho\RegionCityPro\Helper;

use Magento\Framework\App\Cache\Type\Config as CacheConfig;
use Magento\Store\Model\StoreManagerInterface;
use MagePsycho\RegionCityPro\Model\ResourceModel\City\CollectionFactory as CityCollectionFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class City
{
    private $cityJson;

    /**
     * @var CityCollectionFactory
     */
    private $cityCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var CacheConfig
     */
    private $configCacheType;

    public function __construct(
        CityCollectionFactory $cityCollectionFactory,
        StoreManagerInterface $storeManager,
        JsonHelper $jsonHelper,
        CacheConfig $configCacheType
    ) {
        $this->cityCollectionFactory = $cityCollectionFactory;
        $this->storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
        $this->configCacheType = $configCacheType;
    }

    public function getCityData()
    {
        $collection = $this->cityCollectionFactory->create();
        $collection->getSelect()->order(
            new \Zend_Db_Expr('main_table.country_id, main_table.region_id, main_table.default_name ASC')
        );

        $cities = [];
        foreach ($collection as $city) {
            if (!$city->getRegionId() || !$city->getCityId()) {
                continue;
            }

            $cities[$city->getRegionId()][$city->getCityId()] = [
                'code' => $city->getCode(),
                'name' => (string)__($city->getName()),
                'country_code' => $city->getCountryId()
            ];
        }
        return $cities;
    }

    public function getJsonData()
    {
        if (!$this->cityJson) {
            $cacheKey = 'MAGEPSYCHO_REGIONCITYPRO_CITY_JSON_STORE' . $this->storeManager->getStore()->getId();
            $json = $this->configCacheType->load($cacheKey);
            if (empty($json)) {
                $cityData = $this->getCityData();
                $json = $this->jsonHelper->jsonEncode($cityData);
                if ($json === false) {
                    $json = 'false';
                }
                $this->configCacheType->save($json, $cacheKey);
            }
            $this->cityJson = $json;
        }
        return $this->cityJson;
    }
}
