<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DeliveryTime
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Customize\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\DeliveryTime\Helper\Data as MpDtHelper;
use Mageplaza\Customize\Helper\Data;
use Zend_Serializer_Exception;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class DefaultConfigProvider implements ConfigProviderInterface
{
    /**
     * @var MpDtHelper
     */
    protected $mpDtHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * DefaultConfigProvider constructor.
     *
     * @param MpDtHelper $mpDtHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        MpDtHelper $mpDtHelper,
        StoreManagerInterface $storeManager,
        Data $helperData
    ) {
        $this->mpDtHelper = $mpDtHelper;
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        if (!$this->mpDtHelper->isEnabled()) {
            return [];
        }

        $output = [
            'mpDtConfig' => $this->getMpDtConfig()
        ];

        return $output;
    }

    /**
     * @return array
     * @throws \Zend_Serializer_Exception
     */
    private function getMpDtConfig()
    {
        return [
            'isEnabledDeliveryTime'      => $this->mpDtHelper->isEnabledDeliveryTime(),
            'isEnabledHouseSecurityCode' => $this->mpDtHelper->isEnabledHouseSecurityCode(),
            'isEnabledDeliveryComment' => $this->mpDtHelper->isEnabledDeliveryComment(),
            'deliveryDateFormat' => $this->mpDtHelper->getDateFormat(),
            'deliveryDaysOff' => $this->mpDtHelper->getDaysOff(),
            'deliveryDateOff' => $this->mpDtHelper->getDateOff(),
            'deliveryTime' => $this->mpDtHelper->getDeliveryTIme(),
            'allowCountries' => $this->mpDtHelper->getAllowCountries(),
            'cutoffTime' => $this->helperData->getCutoffTime()
        ];
    }
}
