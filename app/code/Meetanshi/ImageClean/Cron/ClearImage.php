<?php

namespace Meetanshi\ImageClean\Cron;

use Meetanshi\ImageClean\Helper\Data;

class ClearImage
{
    private $helper;

    public function __construct(
        Data $helper
    )
    {
        $this->helper = $helper;
    }

    public function execute()
    {
        if ($this->helper->isEnableSchedule()) {
            $frequency = $this->helper->getFrequency();
            $time = explode(',', $this->helper->getStartTime());

            $hours = $time[0];
            $minutes = $time[1];
            $seconds = $time[2];

            if ($frequency == 'daily') {
                if (date("Hi") == "$hours$minutes") {
                    $this->helper->ConfigClear();
                }
            } elseif ($frequency == 'weekly') {
                $weekDay = date('w', strtotime(date("Y/m/d")));
                if (($weekDay == 0 || $weekDay == 6)) {
                    if (date("Hi") == "$hours$minutes") {
                        $this->helper->ConfigClear();
                    }
                }
            } elseif ($frequency == 'monthly') {
                $first = date("Y-m-d", strtotime("first day of this month"));
                $current = date("Y-m-d");

                $diff = date_diff(date_create($first), date_create($current));
                $day = $diff->format("%a");

                if ($day == 0) {
                    if (date("Hi") == "$hours$minutes") {
                        $this->helper->ConfigClear();
                    }
                }
            }
        }
    }
}