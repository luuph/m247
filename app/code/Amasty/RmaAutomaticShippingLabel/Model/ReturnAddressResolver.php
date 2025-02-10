<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Model;

use Magento\Backend\Model\Auth\Session;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Get RMA return address depending on config settings
 */
class ReturnAddressResolver
{
    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        AddressFactory $addressFactory,
        ConfigProvider $configProvider,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CountryFactory $countryFactory,
        RegionFactory $regionFactory,
        Session $session
    ) {
        $this->addressFactory = $addressFactory;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
        $this->session = $session;
    }

    public function getReturnContactName(int $storeId = null): DataObject
    {
        $contactName = new DataObject();

        if ($this->configProvider->getAddressSource() !== OptionSource\ReturnAddressSource::CUSTOM_ADDRESS) {
            if ($admin = $this->session->getUser()) {//todo: maybe change for rma manager
                $contactName->setFirstName($admin->getFirstname());
                $contactName->setLastName($admin->getLastname());
                $contactName->setName($admin->getName());
            }
        } else {
            $name = $this->configProvider->getContactName();
            $contactName->setFirstName('');
            $contactName->setLastName($name);
            $contactName->setName($name);
        }

        return $contactName;
    }

    public function getReturnAddress(int $storeId = null): Address
    {
        /** @var $addressModel Address */
        $addressModel = $this->addressFactory->create();
        $addressData = [];

        switch ($this->configProvider->getAddressSource($storeId)) {
            case OptionSource\ReturnAddressSource::GENERAL_STORE_ADDRESS:
                $addressData = $this->getStoreGeneralAddress($storeId);
                break;
            case OptionSource\ReturnAddressSource::SHIPPING_ORIGIN_ADDRESS:
                $addressData = $this->getShippingOriginAddress($storeId);
                break;
            case OptionSource\ReturnAddressSource::CUSTOM_ADDRESS:
                $addressData = $this->getConfigAddress($storeId);
                break;
        }
        $addressData['country'] = !empty($addressData['countryId'])
            ? $this->countryFactory->create()->loadByCode($addressData['country_id'])->getName()
            : '';
        $region = $this->regionFactory->create()->load($addressData['region_id']);
        $addressData['region_id'] = $region->getCode();
        $addressData['region'] = $region->getName();
        $addressData['company'] = $this->scopeConfig->getValue(
            Information::XML_PATH_STORE_INFO_NAME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $addressData['telephone'] = $this->scopeConfig->getValue(
            Information::XML_PATH_STORE_INFO_PHONE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $addressModel->setData($addressData);
        $addressModel->setCountryId($addressModel->getData('country_id'));
        $addressModel->setStreet($addressModel->getData('street1') . "\n" . $addressModel->getData('street2'));

        return $addressModel;
    }

    private function getStoreGeneralAddress(int $storeId = null): array
    {
        return [
            'city' => $this->scopeConfig->getValue(
                Information::XML_PATH_STORE_INFO_CITY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            'country_id' => $this->scopeConfig->getValue(
                Information::XML_PATH_STORE_INFO_COUNTRY_CODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            'postcode' => $this->scopeConfig->getValue(
                Information::XML_PATH_STORE_INFO_POSTCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            'region_id' => $this->scopeConfig->getValue(
                Information::XML_PATH_STORE_INFO_REGION_CODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            'street2' => $this->scopeConfig->getValue(
                Information::XML_PATH_STORE_INFO_STREET_LINE2,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            'street1' => $this->scopeConfig->getValue(
                Information::XML_PATH_STORE_INFO_STREET_LINE1,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        ];
    }

    private function getShippingOriginAddress(int $storeId = null): array
    {
        return [
            'city' => $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_CITY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            'country_id' => $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            'postcode' => $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_ZIP,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            'region_id' => $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_REGION_ID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            'street2' => $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_ADDRESS2,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            'street1' => $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_ADDRESS1,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        ];
    }

    private function getConfigAddress(int $storeId = null): array
    {
        return $this->configProvider->getCustomAddress($storeId);
    }
}
