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
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderDeliveryDate\Model;

use Magento\Customer\Block\Widget\Dob;
use Magento\Framework\Serialize\Serializer\Json;

class CompositeConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const TIME_OF_DAY_IN_SECONDS = 86400;

    /**
     * @var \Bss\OrderDeliveryDate\Helper\Data
     */
    protected $bssHelper;

    /**
     * @var \Magento\Config\Model\Config\Source\Locale\Weekdays
     */
    protected $weekdays;

    /**
     * @var Dob
     */
    protected $date;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * CompositeConfigProvider constructor.
     * @param \Bss\OrderDeliveryDate\Helper\Data $bssHelper
     * @param \Magento\Config\Model\Config\Source\Locale\Weekdays $weekdays
     * @param Dob $date
     * @param Json $serializer
     */
    public function __construct(
        \Bss\OrderDeliveryDate\Helper\Data $bssHelper,
        \Magento\Config\Model\Config\Source\Locale\Weekdays $weekdays,
        Dob $date,
        Json $serializer
    ) {
        $this->bssHelper = $bssHelper;
        $this->weekdays = $weekdays;
        $this->date = $date;
        $this->serializer = $serializer;
    }

    /**
     * Add ODD variable to Checkout Page
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $output = [];
        $translateCalendar = $this->serializer->unserialize($this->date->getTranslatedCalendarConfigJson());
        if ($this->bssHelper->isEnabled()) {
            $output['bss_delivery_enable'] = (boolean) $this->bssHelper->isEnabled();
            if ($this->bssHelper->getTimeSlot()) {
                $output['bss_delivery_timeslot'] = $this->bssHelper->getTimeSlot();
                $output['bss_delivery_has_timeslot'] = true;
            }
            $day_off = $this->bssHelper->getDayOff();
            $block_out_holidays = $this->bssHelper
                ->returnClassSerialize()
                ->unserialize($this->bssHelper->getBlockHoliday());
            $current_time = (int) $this->bssHelper->getStoreTimestamp();
            $cut_off_time_convert = $this->bssHelper->getCutOffTime();
            $process_time = $this->bssHelper->getProcessingTime();

            if ($cut_off_time_convert &&
                $current_time > $cut_off_time_convert
            ) {
                $process_time++;
            }

            $block_out_holidays = !empty($block_out_holidays) ? json_encode($block_out_holidays) : '';
            $output['bss_shipping_comment'] = (boolean) $this->bssHelper->isShowShippingComment();
            $output['bss_delivery_block_out_holidays'] = $block_out_holidays;
            $output['bss_delivery_day_off'] = $day_off;
            $output['bss_delivery_date_fomat'] = $this->bssHelper->getDateFormat();
            $output['bss_delivery_current_time'] = $current_time;
            $output['bss_delivery_time_zone'] = $this->bssHelper->getTimezoneOffsetSeconds();
            $output['as_processing_days'] = $this->bssHelper->isAsProcessingDays();
            $output['store_time_zone'] = $this->bssHelper->getStoreTimezone();
            if ($this->bssHelper->getIcon()) {
                $output['bss_delivery_icon'] = $this->bssHelper->getIcon();
            }
            $output['date_field_required'] = $this->bssHelper->isFieldRequired('required_date');
            $output['times_field_required'] = $this->bssHelper->isFieldRequired('required_timeslot');
            $output['comment_field_required'] = $this->bssHelper->isFieldRequired('required_comment');
            $output['on_which_page'] = $this->bssHelper->getDisplayAt();
            $output['action_payment_save'] = $this->bssHelper->getPaymentSaveAction();
            $output['today_date'] = $this->bssHelper->getDateToday();
            $cutOffTime = $cut_off_time_convert ?? 0;
            $output['min_date'] = $this->getMindate($day_off, $block_out_holidays, $process_time, $current_time, $cutOffTime);
            $output['bss_delivery_process_time'] = $process_time;
            if ($translateCalendar) {
                $output['month_names'] = $translateCalendar['monthNames'];
                $output['month_names_short'] = $translateCalendar['monthNamesShort'];
                $output['day_names'] = $translateCalendar['dayNames'];
                $output['day_names_short'] = $translateCalendar['dayNamesShort'];
                $output['day_names_min'] = $translateCalendar['dayNamesMin'];
            }
        }
        return $output;
    }

    /**
     * Whatever we should add to day to processing day
     *
     * @return bool
     * @deprecated
     */
    public function isProcessingDayDisabled()
    {
        if ($this->bssHelper->isAsProcessingDays()) {
            return false;
        }
        $weekDays = $this->weekdays->toOptionArray();
        $dayOff = explode(',', $this->bssHelper->getDayOff());
        $disableDayName = [];
        foreach ($weekDays as $weekDay) {
            if (isset($weekDay['value']) &&
                isset($weekDay['label']) &&
                in_array($weekDay['value'], $dayOff)) {
                $disableDayName[] = strtolower($weekDay['label']);
            }
        }
        if (in_array(strtolower($this->bssHelper->getDayOfWeekName()), $disableDayName)) {
            return true;
        }
        return false;
    }

    /**
     * Get min date
     *
     * @param string $day_off
     * @param array|string $block_out_holidays
     * @param int|string $process_time
     * @param int $current_time
     * @param int $cutOffTime
     * @return array|int|string
     */
    protected function getMindate($day_off, $block_out_holidays, &$process_time, $current_time, $cutOffTime)
    {
        // If exclude processing day = no, then return config processing time
        if (!$this->bssHelper->isAsProcessingDays()) {
            return $process_time;
        }
        // If processing time <= 0, then return config processing time
        if ($process_time <= 0) {
            return $process_time;
        }
        // If day off is empty, then return config processing time
        $dayOffArr = $this->getDayAsArray($day_off);
        $holidays = $this->getDayAsArray($block_out_holidays);
        $timeOfDayInSeconds = self::TIME_OF_DAY_IN_SECONDS;

        if (!empty($dayOffArr) || !empty($holidays)) {
            for ($i = 0; $i < $process_time; $i++) {
                $nextOfDayInTime = $current_time + $i * $timeOfDayInSeconds;
                $momentDate = date('Y-m-d', $nextOfDayInTime);
                $momentDay = date('w', $nextOfDayInTime);

                // Fix adding 2 times the date handling time if now the date is disabled and execute after the cutoff time.
                if ($momentDate === date('Y-m-d', $cutOffTime) &&
                    $nextOfDayInTime > $cutOffTime
                ) {
                    continue;
                }

                if (is_array($dayOffArr) && in_array($momentDay, $dayOffArr)) {
                    $process_time++;
                } elseif (is_array($holidays) && strpos(implode(",", $holidays), $momentDate) !== false) {
                    $process_time ++;
                }
            }
        }

        $newTime = $process_time * $timeOfDayInSeconds + $current_time;

        return [
            'dayOfWeek' => date('w', $newTime),
            'extendedDay' => ($process_time < 7) ? 0 : ($process_time - ($process_time % 7)) / 7
        ];
    }

    /**
     * Get day as array
     *
     * @param $days
     * @return array
     */
    private function getDayAsArray($days)
    {
        return is_string($days) ? explode(',', $days) : (is_array($days) ? $days : []);
    }
}
