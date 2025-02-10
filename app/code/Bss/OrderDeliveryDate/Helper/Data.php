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
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\OrderDeliveryDate\Helper;

use Magento\Framework\App\State;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serialize;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var ConvertDate
     */
    protected $helperDate;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulation;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $currencyFactory;

    /**
     * @var string|null
     */
    protected $bssQuote = null;

    /**
     * @var State
     */
    protected $state;

    public const IS_FIELD_REQUIRED_DATE = "required_date";
    public const IS_FIELD_REQUIRED_TIME_SLOT = "required_timeslot";
    public const IS_FIELD_REQUIRED_COMMENT = "required_comment";

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param TimezoneInterface $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Bss\OrderDeliveryDate\Helper\ConvertDate $helperDate
     * @param \Magento\Store\Model\App\Emulation $emulation
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param PriceCurrencyInterface $currencyFactory
     * @param State $state
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Bss\OrderDeliveryDate\Helper\ConvertDate $helperDate,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        PriceCurrencyInterface $currencyFactory,
        State $state
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->localeDate = $localeDate;
        $this->date = $date;
        $this->storeManager = $storeManager;
        $this->serialize = $serialize;
        $this->file = $file;
        $this->productMetadata = $productMetadata;
        $this->helperDate = $helperDate;
        $this->emulation = $emulation;
        $this->currencyFactory = $currencyFactory;
        $this->quoteFactory = $quoteFactory;
        $this->state = $state;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Filesystem\Driver\File
     */
    public function returnDriverFile()
    {
        return $this->file;
    }

    /**
     * @return \Magento\Framework\Serialize\Serializer\Serialize
     */
    public function returnClassSerialize()
    {
        return $this->serialize;
    }

    /**
     * Check module is enable
     *
     * @param int $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        $active =  $this->scopeConfig->getValue(
            'orderdeliverydate/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($active != 1) {
            return false;
        }

        return true;
    }

    /**
     * Check show shipping comment
     *
     * @param int $storeId
     * @return bool
     */
    public function isShowShippingComment($storeId = null)
    {
        $active =  $this->scopeConfig->getValue(
            'orderdeliverydate/general/shipping_comment',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($active != 1) {
            return false;
        }

        return true;
    }

    /**
     * Exclude Holidays/Disable Day From Processing Day.
     *
     * @param int $storeId
     * @return bool
     */
    public function isAsProcessingDays($storeId = null)
    {
        $active =  $this->scopeConfig->getValue(
            'orderdeliverydate/general/as_processing_days',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($active != 1) {
            return false;
        }

        return true;
    }

    /**
     * Choose at which page delivery date should be captured.
     *
     * @param int $storeId
     * @return string
     */
    public function getDisplayAt($storeId = null)
    {
        $active =  $this->scopeConfig->getValue(
            'orderdeliverydate/general/on_which_page',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $active;
    }

    /**
     * Get processing time
     *
     * @param int $storeId
     * @return int
     */
    public function getProcessingTime($storeId = null)
    {
        $process_time =  $this->scopeConfig->getValue(
            'orderdeliverydate/general/process_time',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$process_time) {
            return 0;
        }
        return $process_time;
    }

    /**
     * Get cut off time
     *
     * @param int $storeId
     * @return bool|false|int
     */
    public function getCutOffTime($storeId = null)
    {
        $cut_off_time =  $this->scopeConfig->getValue(
            'orderdeliverydate/general/cut_off_time',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$cut_off_time || $cut_off_time == '00,00,00') {
            return false;
        }

        $cutOffDate = $this->localeDate->date()->format('Y-m-d') . ' ' . str_replace(',', ':', $cut_off_time);
        $cut_off_time_convert = strtotime($cutOffDate);

        return $cut_off_time_convert;
    }

    /**
     * Get the days off
     *
     * @param int $storeId
     * @return string|array
     */
    public function getBlockHoliday($storeId = null)
    {
        $block_out_holidays =  $this->scopeConfig->getValue(
            'orderdeliverydate/general/block_out_holidays',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $response = [];
        if ($block_out_holidays) {
            $block_out_holidays_arr = $this->serialize->unserialize($block_out_holidays);
            if ($storeId) {
                return $block_out_holidays_arr;
            }
            if ($block_out_holidays_arr) {
                foreach ($block_out_holidays_arr as $holidays) {
                    $newDate = date("Y-m-d", strtotime($holidays['date']));
                    $response[] = $newDate;
                }
            }
        }
        if ($storeId) {
            return $response;
        }
        return $this->serialize->serialize($response);
    }

    /**
     * Get the time slot
     *
     * @param int $storeId
     * @return array
     */
    public function getTimeSlot($storeId = null)
    {
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
        $processingDay = $this->getProcessingTime($storeId);
        $now = $this->getStoreTimestamp();
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
                if ($this->timeLineCondition($timeFrom, $timeTo, $now) && $processingDay == 0) {
                    $disabled = 1;
                }
                $timeSlotPrice = $this->currencyFactory->convert($timeSlot['price'], $storeId);
                $timeSlotPriceLabel = $this->currencyFactory->format($timeSlotPrice, false, 2, $storeId);
                $timeSlotLabel = $timeSlot['name'] . ' | ' . $timeSlot['from'] . ' - ' . $timeSlot['to'] . ' | '
                    . $timeSlot['note'] . ' (+' . $timeSlotPriceLabel . ')';
                $timeSlotValueArray = ['value' => $timeSlotLabel, 'label' => $timeSlotLabel, 'disabled' => $disabled,
                    'name' => $timeSlot['name'], 'price' => $timeSlotPrice, 'base_price' => $timeSlot['price']];
                array_push($result, $timeSlotValueArray);
            }
        }
        return $result;
    }

    /**
     * @param $timeFrom
     * @param $timeTo
     * @param $now
     * @return bool
     */
    public function timeLineCondition($timeFrom, $timeTo, $now)
    {
        // $timeFrom < $now < $timeTo
        // $timeFrom, $timeTo < $now
        // are disabled
        return (($timeFrom < $now && $timeTo > $now) || ($timeTo < $now && $timeFrom < $now));
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

    /**
     * Get the days of in a week
     *
     * @param int $storeId
     * @return bool|mixed
     */
    public function getDayOff($storeId = null)
    {
        $day_off =  $this->scopeConfig->getValue(
            'orderdeliverydate/general/deliverydate_day_off',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($day_off === null) {
            return false;
        }
        return $day_off;
    }

    /**
     * Get the icon calendar
     *
     * @param int $storeId
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIcon($storeId = null)
    {
        $icon =  $this->scopeConfig->getValue(
            'orderdeliverydate/general/icon_calendar',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!isset($icon)) {
            return false;
        }
        return $this->getMediaUrl() . 'bss/deliverydate/' . $icon;
    }

    /**
     * Get date format
     *
     * @param int $storeId
     * @return string
     */
    public function getDateFormat($storeId = null)
    {
        $dateFormat = $this->scopeConfig->getValue(
            'orderdeliverydate/general/date_fields',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($this->state->getAreaCode() == 'graphql') {
            return $dateFormat;
        }
        if ($dateFormat) {
            switch ($dateFormat) {
                case 1:
                    return 'mm/dd/yy';
                case 2:
                    return 'dd-mm-yy';
                case 3:
                    return 'yy-mm-dd';
                default:
                    return 'yy/mm/dd';
            }
        }
        return 'yy/mm/dd';
    }

    /**
     * @param null $date
     * @return string
     */
    public function formatDate($date = null)
    {
        $dateFormat = $this->getDateFormat();
        if ($dateFormat) {
            switch ($dateFormat) {
                case 'mm/dd/yy':
                    $dateFormat = 'm/d/Y';
                    break;
                case 'dd-mm-yy':
                    $dateFormat = 'd-m-Y';
                    break;
                case 'yy-mm-dd':
                    $dateFormat = 'Y-m-d';
                    break;
                default:
                    $dateFormat = 'm/d/y';
                    break;
            }
        }
        if ($date) {
            return $this->helperDate->scopeDate(null, $date, false)->format($dateFormat);
        } else {
            return $dateFormat;
        }
    }

    /**
     * Get store timestamp
     *
     * @param int $store
     * @return int
     */
    public function getStoreTimestamp($store = null)
    {
        return $this->localeDate->scopeTimeStamp($store);
    }

    /**
     * @return int
     */
    public function getTimezoneOffsetSeconds()
    {
        return $this->date->getGmtOffset();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Check field required
     *
     * @param string $field
     * @param int $storeId
     * @return bool
     */
    public function isFieldRequired($field, $storeId = null)
    {
        $enable =  $this->scopeConfig->getValue(
            'orderdeliverydate/general/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($enable != 1) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPaymentSaveAction()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $action = $baseUrl . "orderdeliverydate/payment/saveDelivery";
        return $action;
    }

    /**
     * @return string
     */
    public function getDateToday()
    {
        $dateFormat = $this->getDateFormat();
        if ($dateFormat) {
            switch ($dateFormat) {
                case 'mm/dd/yy':
                    $dateFormat = 'm/d/Y';
                    break;
                case 'dd-mm-yy':
                    $dateFormat = 'd-m-Y';
                    break;
                case 'yy-mm-dd':
                    $dateFormat = 'Y-m-d';
                    break;
                default:
                    $dateFormat = 'm/d/y';
                    break;
            }
        }
        return $this->date->date($dateFormat);
    }

    /**
     * @param null $scopeType
     * @param null $scopeCode
     * @return string
     */
    public function getStoreTimezone($scopeType = null, $scopeCode = null)
    {
        return $this->localeDate->getConfigTimezone($scopeType, $scopeCode);
    }

    /**
     * @return false|string
     */
    public function getDayOfWeekName()
    {
        return $this->localeDate->date()->format('l');
    }

    /**
     * @return bool
     */
    public function isLowerThan241Version()
    {
        $version = $this->productMetadata->getVersion();
        $checkVersion = version_compare($version, '2.4.0', '<=');
        $checkVersion1 = version_compare($version, '2.3.6', '!=');
        return $checkVersion && $checkVersion1;
    }

    /**
     * @return \Magento\Store\Model\App\Emulation
     */
    public function getEmulationContext()
    {
        return $this->emulation;
    }

    /**
     * Function convert base currency to current currency
     *
     * @param $amount
     * @param $currencyCode
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function convertCurrency($amount, $currencyCode)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $currency = $objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode);
        return $this->currencyFactory->convert($amount, $storeId, $currency);
    }

    /**
     * Function convert current currency to base currency
     *
     * @param $amount
     * @return float|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function convertToBaseCurrency($amount)
    {
        $store = $this->storeManager->getStore()->getStoreId();
        if ($amount > 0) {
        $rate = $this->currencyFactory->convert($amount, $store) / $amount;
        return $amount / $rate;
        }
        return 0;
    }
}
