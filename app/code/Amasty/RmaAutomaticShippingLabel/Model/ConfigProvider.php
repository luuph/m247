<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Model;

use Amasty\Base\Model\Serializer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    public const RETURN_ADDRESS_SOURCE = 'return_address/address_source';
    public const CONTACT_NAME = 'return_address/contact_name';
    public const COUNTRY_ID = 'return_address/country_id';
    public const REGION_ID = 'return_address/region_id';
    public const POSTCODE = 'return_address/postcode';
    public const CITY = 'return_address/city';
    public const STREET_LINE_1 = 'return_address/street_line1';
    public const STREET_LINE_2 = 'return_address/street_line2';

    /**
     * @var string
     */
    protected $pathPrefix = 'amrmashiplabel/';
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Serializer $serializer
    ) {
        parent::__construct($scopeConfig);
        $this->serializer = $serializer;
    }

    public function getAddressSource($storeId = null): int
    {
        return (int)$this->getValue(self::RETURN_ADDRESS_SOURCE, $storeId);
    }

    public function getContactName($storeId = null): string
    {
        return $this->getValue(self::CONTACT_NAME, $storeId);
    }

    public function isCarrierEnabledForRma(string $carrierCode, $storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'carriers/' . $carrierCode . '/active_amrma',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCustomAddress($storeId = null): array
    {
        return [
            'country_id' => $this->getValue(self::COUNTRY_ID, $storeId),
            'region_id' => $this->getValue(self::REGION_ID, $storeId),
            'postcode' => $this->getValue(self::POSTCODE, $storeId),
            'city' => $this->getValue(self::CITY, $storeId),
            'street1' => $this->getValue(self::STREET_LINE_1, $storeId),
            'street2' => $this->getValue(self::STREET_LINE_2, $storeId)
        ];
    }
}
