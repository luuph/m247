<?php
/**
 * @category Mageants StoreLocator
 * @package Mageants_StorePickup
 * @copyright Copyright (c) 2017 Mageants
 * @author Mageants Team <support@mageants.com>
 */
 
namespace Mageants\StoreLocator\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class StorePickupConfigProvider implements ConfigProviderInterface
{
    const XPATH_Enabled = 'carriers/storepickup/active';
    const XPATH_FORMAT = 'carriers/storepickup/format';
    const XPATH_DISABLED = 'carriers/storepickup/disabled';
    const XPATH_HOURMIN = 'carriers/storepickup/hourMin';
    const XPATH_HOURMAX = 'carriers/storepickup/hourMax';
    const XPATH_STORE_LOCATIONS = 'carriers/storepickup/store_locations';
    const XPATH_DISABLE_DAYS = 'carriers/storepickup/disbleday';
    const XPATH_SHOW_ADD_CHECKOUT = 'StoreLocator/general/showaddcheckout';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

     /**
     * Json Serializer
     *
     * @var JsonSerializer
     */
    protected $jsonSerializer;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        JsonSerializer $jsonSerializer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) 
    {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $storeId = $this->getStoreId();
        $enabled = $this->scopeConfig->getValue(self::XPATH_Enabled, ScopeInterface::SCOPE_STORE, $storeId);
        $disabled = $this->scopeConfig->getValue(self::XPATH_DISABLED, ScopeInterface::SCOPE_STORE, $storeId);
        $hourMin = $this->scopeConfig->getValue(self::XPATH_HOURMIN, ScopeInterface::SCOPE_STORE, $storeId);
        $hourMax = $this->scopeConfig->getValue(self::XPATH_HOURMAX, ScopeInterface::SCOPE_STORE, $storeId);
        $format = $this->scopeConfig->getValue(self::XPATH_FORMAT, ScopeInterface::SCOPE_STORE, $storeId);
        $disableDays = $this->scopeConfig->getValue(self::XPATH_DISABLE_DAYS, ScopeInterface::SCOPE_STORE, $storeId);
        $stores =$this->jsonSerializer->unserialize($this->scopeConfig->getValue(self::XPATH_STORE_LOCATIONS, ScopeInterface::SCOPE_STORE, $storeId));
        $showAddCheckout = $this->scopeConfig->getValue(self::XPATH_SHOW_ADD_CHECKOUT, ScopeInterface::SCOPE_STORE, $storeId);

        $storeLocation = '';
        $storeCount = 0;
        if($showAddCheckout == 1){
             $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
             $collecion = $objectManager->create('Mageants\StoreLocator\Block\Index');
             $stores=$collecion->getStoreCollection(); 
             $storeCount = count($stores);
             if($stores){
              foreach($stores as $store)
                {
                   $country = $objectManager->create('\Magento\Directory\Model\Country')->load($store['country'])->getName();
                   $storeLocation[] = $store['address'].', '.$store['city'].', '.$country.','.$store['phone'];
                }
            }
        }
        /*else{
            $storeCount = count($stores);
             if($stores)
            {
                foreach($stores as $store)
                {
                    $storeLocation[] = $store['title'].', '.$store['street'].', '.$store['phone'];
                } 
            }
        }      */
        
        $noday = 0;
        if($disabled == -1) {
            $noday = 1;
        }

        $config = [
            'shipping' => [
                'store_pickup' => [
                    'enableextension' => $enabled,
                    'format' => $format,
                    'disabled' => $disabled,
                    'noday' => $noday,
                    'hourMin' => $hourMin,
                    'hourMax' => $hourMax,
                    'stores' => $storeLocation,
                    'disableDays' => $disableDays,
                    'storeCount' => $storeCount
                ]
            ]
        ];
        return $config;
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }
}
