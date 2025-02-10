<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Plugin;

use Amasty\RmaAutomaticShippingLabel\Model\ConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Add shipping label carriers to RMA config values
 */
class RmaConfigProvider
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigProvider $configProvider
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configProvider = $configProvider;
    }

    public function afterGetCarriers(
        \Amasty\Rma\Model\ConfigProvider $subject,
        array $rmaCarriers,
        $storeId = null,
        $toArray = false
    ): array {
        $existingCodes = [];

        foreach ($rmaCarriers as $carrier) {
            $existingCodes[] = $carrier['code'] ?? '';
        }
        $allCarriers = $this->scopeConfig->getValue('carriers', ScopeInterface::SCOPE_STORE, $storeId);

        foreach ($allCarriers as $carrierCode => $carrierData) {
            if ($this->configProvider->isCarrierEnabledForRma($carrierCode)
                && !in_array($carrierCode, $existingCodes)
            ) {
                if ($toArray) {
                    $rmaCarriers[$carrierCode] = $carrierData['title'] ?? '';
                } else {
                    $rmaCarriers[] = [
                        'code' => $carrierCode,
                        'label' => $carrierData['title'] ?? '',
                    ];
                }
            }
        }

        return $rmaCarriers;
    }
}
