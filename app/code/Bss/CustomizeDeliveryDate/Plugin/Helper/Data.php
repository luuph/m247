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
 * @package    Bss_CustomizeDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomizeDeliveryDate\Plugin\Helper;

class Data
{
    protected $self;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var Serialize
     */
    protected $serialize;

    /**
     * @var PriceCurrencyInterface
     */
    protected $currencyFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $currencyFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Framework\Pricing\PriceCurrencyInterface $currencyFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->date = $date;
        $this->serialize = $serialize;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * Customize time slot label
     *
     * @param \Bss\OrderDeliveryDate\Helper\Data $subject
     * @param array $result
     * @param int $storeId
     * @return void
     */
    public function afterGetTimeSlot($subject, $result, $storeId = null)
    {
        $this->self = $subject;
        $time_slots = $this->scopeConfig->getValue(
            'orderdeliverydate/general/time_slots',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($time_slots) {
            $time_slot_arr = $this->serialize->unserialize($time_slots);
            return $this->getClearTimeSlot($time_slot_arr, $storeId);
        }
        return [];
    }

    /**
     * Get clear time slot
     *
     * @param array $timeSlotArr
     * @param int $storeId
     * @return array
     */
    protected function getClearTimeSlot($timeSlotArr, $storeId = null)
    {
        $result = [];
        $processingDay = $this->self->getProcessingTime($storeId);
        $now = $this->self->getStoreTimestamp();
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        if (is_array($timeSlotArr) && !empty($timeSlotArr)) {
            foreach ($timeSlotArr as $timeSlot) {
                $timeFrom = $this->convertAMPM($timeSlot['from']);
                $timeTo = $this->convertAMPM($timeSlot['to']);
                if (!$timeFrom || !$timeTo) {
                    continue;
                }
                $disabled = 0;
                if ($this->self->timeLineCondition($timeFrom, $timeTo, $now) && $processingDay == 0) {
                    $disabled = 1;
                }
                $timeSlotPrice = $this->currencyFactory->convert($timeSlot['price'], $storeId);
                $timeSlotPriceLabel = $this->currencyFactory->format($timeSlotPrice, false, 2, $storeId);
                // Start Customize remove price to label
                $timeSlotValue = $timeSlot['name'] . ' | ' . $timeSlot['from'] . ' - ' . $timeSlot['to'] . ' | '
                    . $timeSlot['note'] . ' (+' . $timeSlotPriceLabel . ')';
                $timeSlotLabel = $timeSlot['name'] . ' | ' . $timeSlot['from'] . ' - ' . $timeSlot['to'];
                if($timeSlot['note'] !== "" ) {
                    $timeSlotLabel = $timeSlotLabel. ' | '. $timeSlot['note'];
                }
                $timeSlotValueArray = ['value' => $timeSlotValue, 'label' => $timeSlotLabel, 'disabled' => $disabled,
                    'name' => $timeSlot['name'], 'price' => $timeSlotPrice, 'base_price' => $timeSlot['price']];
                array_push($result, $timeSlotValueArray);
            }
        }
        return $result;
    }

    /**
     * @param string $strTime
     * @return false|int
     */
    protected function convertAMPM($strTime)
    {
        $exp = "/^[0-9][0-9]:[0-9][0-9]\s[AM|PM]/i"; // Regex check AM PM time
        if (preg_match($exp, $strTime)) {
            return $this->date->gmtTimestamp(date('Y-m-d') . ' ' . $strTime);
        }
        return false;
    }

}