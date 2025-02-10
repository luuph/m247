<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Model;

use Amasty\RmaReports\Model\OptionSource\Date;
use Magento\Framework\App\RequestInterface;

class DateProcessor
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    private $timezone;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        RequestInterface $request
    ) {
        $this->timezone = $timezone;
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getFromToDate()
    {
        switch ((int)$this->request->getParam('date_range')) {
            case Date::WEEK:
                $dateFrom = $this->parseDate(date('Y-m-d', strtotime('-7 day')));
                $dateTo = $this->parseDate(date('Y-m-d', time()) . Date::DAY_END_POSTFIX);
                break;
            case Date::MONTH:
                $dateFrom = $this->parseDate(date('Y-m-d', strtotime('-30 day')));
                $dateTo = $this->parseDate(date('Y-m-d', time()) . Date::DAY_END_POSTFIX);
                break;
            case Date::YEAR:
                $dateFrom = $this->parseDate(date('Y-m-d', strtotime('-1 year')));
                $dateTo = $this->parseDate(date('Y-m-d', time()) . Date::DAY_END_POSTFIX);
                break;
            case Date::CUSTOM:
                $dateFrom = $this->parseDate($this->request->getParam('start_date'));
                $dateTo = $this->parseDate($this->request->getParam('end_date', time()) . Date::DAY_END_POSTFIX);
                break;
            default:
                $dateFrom = $this->parseDate(date('Y-m-d', 0));
                $dateTo = $this->parseDate(date('Y-m-d', time()). Date::DAY_END_POSTFIX);
        }

        return [$dateFrom, $dateTo];
    }

    /**
     * @param $date
     * @return null|\DateTime
     */
    public function parseDate($date)
    {
        try {
            $date = new \DateTime(
                $date,
                new \DateTimeZone($this->timezone->getConfigTimezone())
            );
            $date->setTimezone(new \DateTimeZone('UTC'));
        } catch (\Exception $e) {
            return null;
        }

        return $date;
    }
}
