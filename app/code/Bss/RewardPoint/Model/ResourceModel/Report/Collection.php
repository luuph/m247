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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\RewardPoint\Model\ResourceModel\Report;

use Bss\RewardPoint\Helper\Data;
use DateTime;
use DateTimeInterface;
use Exception;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroup;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\ResourceModel\Report\Collection\Factory;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Safe\Exceptions\DatetimeException;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * From value
     *
     * @var DateTimeInterface
     */
    protected $from;

    /**
     * To value
     *
     * @var DateTimeInterface
     */
    protected $to;

    /**
     * Report period
     *
     * @var int
     */
    protected $period;

    /**
     * Report currency
     *
     * @var string
     */
    protected $currency;

    /**
     * @var int
     */
    protected $intervals;

    /**
     * Intervals
     *
     * @var int
     */
    protected $reports;

    /**
     * @var int
     */
    protected $pageSize;

    /**
     * Array of store ids
     *
     * @var array
     */
    protected $storeIds;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var Factory
     */
    protected $collectionFactory;

    /**
     * @var CustomerGroup
     */
    protected $groupCollectionFactory;

    /**
     * @var EarnCollectionFactory
     */
    protected $earnCollectionFactory;

    /**
     * @var int
     */
    protected $customerGroup;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var WebsiteCollectionFactory
     */
    protected $websiteCollectionFactory;

    /**
     * @var WebsiteCollectionFactory
     */
    protected $website;

    /**
     * @param EntityFactory $entityFactory
     * @param TimezoneInterface $localeDate
     * @param Factory $collectionFactory
     * @param CustomerGroup $groupCollectionFactory
     * @param EarnCollectionFactory $earnCollectionFactory
     * @param Data $helperData
     * @param WebsiteCollectionFactory $websiteCollectionFactory
     */
    public function __construct(
        EntityFactory            $entityFactory,
        TimezoneInterface        $localeDate,
        Factory                  $collectionFactory,
        CustomerGroup            $groupCollectionFactory,
        EarnCollectionFactory    $earnCollectionFactory,
        Data                     $helperData,
        WebsiteCollectionFactory $websiteCollectionFactory
    ) {
        $this->localeDate = $localeDate;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($entityFactory);
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->earnCollectionFactory = $earnCollectionFactory;
        $this->dataHelper = $helperData;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     *
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        $this->_items = $this->getReports();
        return $this;
    }

    /**
     * Get Reports based on intervals
     *
     * @return array
     * @throws LocalizedException
     */
    public function getReports()
    {
        if (!$this->reports) {
            $reports = [];
            $emptyInterval = new DataObject([
                'period' => "",
                'start' => "",
                'end' => ""
            ]);
            foreach ($this->getIntervals() as $interval) {
                $interval->setChildren($this->getReport($interval->getStart(), $interval->getEnd()));
                $intervalEmpty = false;
                $emptyInterval = $interval;
                if (count($interval->getChildren()) == 0) {
                    $intervalEmpty = true;
                    $emptyInterval->setIsEmpty(true);
                } else {
                    foreach ($interval->getChildren()->getItems() as $item) {
                        if ($item->getData()['total_earn_point'] == 0 || $item->getData()['total_earn_point'] == null) {
                            $emptyInterval->setIsEmpty(true);
                            $intervalEmpty = true;
                        }
                    }
                }
                if (!$intervalEmpty) {
                    $reports[] = $interval;
                }
            }
            if (count($reports) == 0) {
                $emptyInterval->setData('period', "");
                $reports[] = $emptyInterval;
            }
            $this->reports = $reports;
        }
        return $this->reports;
    }

    /**
     * Get intervals
     *
     * @return array
     * @throws LocalizedException
     * @throws Exception
     */
    protected function getIntervals()
    {
        if (!$this->intervals) {
            $this->intervals = [];

            $dateStart = new \DateTime($this->from->format('Y-m-d'), $this->from->getTimezone());
            $dateEnd = new \DateTime($this->to->format('Y-m-d'), $this->to->getTimezone());

            if ($this->period == 'overall') {
                $intervalOverall = [];
                if (!$this->from && !$this->to) {
                    $intervalOverall = $this->getOverallInterval();
                } else {
                    $intervalOverall['period'] = $dateStart->format('m/d/Y') . " - " . $dateEnd->format('m/d/Y');
                    $intervalOverall['start'] = $this->localeDate->convertConfigTimeToUtc(
                        $dateStart->format('Y-m-d 00:00:00')
                    );
                    $intervalOverall['end'] = $this->localeDate->convertConfigTimeToUtc(
                        $dateEnd->format('Y-m-d 23:59:59')
                    );
                }
                $this->intervals[$intervalOverall['period']] = new DataObject($intervalOverall);
                return $this->intervals;
            }

            $firstDayWeek = 1;
            if ($this->period == "week") {
                //0 is sunday, 6 is saturday
                $firstDayWeek = (int)$this->dataHelper->getFirstDayOfWeek();
                //1 is monday, 7 is sunday
                $firstDayWeek = $firstDayWeek == 0 ? 7 : $firstDayWeek;
            }

            $firstInterval = true;

            while ($dateStart <= $dateEnd) {
                $interval = [];
                switch ($this->period) {
                    case 'day':
                        $interval = $this->getDayInterval($dateStart);
                        $dateStart->modify('+1 day');
                        break;
                    case 'week':
                        //0 is sunday, 6 is saturday
                        $interval = $this->getWeekInterval($dateStart, $dateEnd, $firstInterval, $firstDayWeek);
                        break;
                    case 'month':
                        $interval = $this->getMonthInterval($dateStart, $dateEnd, $firstInterval);
                        break;
                    case 'year':
                        $interval = $this->getYearInterval($dateStart, $dateEnd, $firstInterval);
                        break;
                    default:
                        $interval = $this->getOverallInterval();
                        break;
                }
                $firstInterval = false;
                if (!$firstInterval) {
                    $this->intervals[$interval['period']] = new DataObject($interval);
                }
            }
        }
        return $this->intervals;
    }

    /**
     * Set period
     *
     * @codeCoverageIgnore
     *
     * @param int $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }



    /**
     * Set interval
     *
     * @codeCoverageIgnore
     *
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    public function setInterval($fromDate, $toDate)
    {
        $this->from = $fromDate;
        $this->to = $toDate;

        return $this;
    }

    /**
     * Return date periods
     *
     * @return array
     */
    public function getPeriods()
    {
        return [
            'day' => __('Day'),
            'week' => __('Week'),
            'month' => __('Month'),
            'year' => __('Year'),
            'overall' => __('Overall')
        ];
    }

    /**
     * Get customer group
     *
     * @return string[]
     */
    public function getCustomerGroup()
    {
        $customerGroups = $this->groupCollectionFactory->create();
        $listCustomerGroup = [-1 => 'All Customer Groups'];
        foreach ($customerGroups as $customerGroup) {
            $listCustomerGroup[] = $customerGroup->getCode();
        }
        return $listCustomerGroup;
    }

    /**
     * Get currencies
     *
     * @return string[]
     */
    public function getCurrencies()
    {
        return $this->dataHelper->getAllowedCurrencies();
    }

    /**
     * Get website
     *
     * @return string[]
     */
    public function getAllWebsite()
    {
        $websites = $this->websiteCollectionFactory->create();
        $listWebsite = [0 => "All Websites"];
        foreach ($websites as $website) {
            $listWebsite[$website->getId()] = $website->getName();
        }
        return $listWebsite;
    }

    /**
     * Set customer group
     *
     * @param int $customerGroup
     * @return $this
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;
        return $this;
    }

    /**
     * Set website
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->website = $websiteId;
        return $this;
    }

    /**
     * Get size
     *
     * @return int
     * @throws LocalizedException
     */
    public function getSize()
    {
        return count($this->getIntervals());
    }

    /**
     * Get interval when using overall
     *
     * @return array
     */
    protected function getOverallInterval()
    {
        $interval = [];
        $interval['period'] = "Overall";
        $interval['start'] = $this->getDateStart();
        $interval['end'] = date_create()->format('Y-m-d H:i:s');
        return $interval;
    }

    /**
     * Get date of first record
     *
     * @return mixed|null
     */
    public function getDateStart()
    {
        return $this->earnCollectionFactory->create()->getFirstItem()['created_at'];
    }

    /**
     * Get interval for a day
     *
     * @param \DateTime $dateStart
     * @return array
     * @throws LocalizedException
     */
    protected function getDayInterval($dateStart)
    {
        return [
            'period' => $dateStart->format('m/d/Y'),
            'start' => $this->localeDate->convertConfigTimeToUtc($dateStart->format('Y-m-d 00:00:00')),
            'end' => $this->localeDate->convertConfigTimeToUtc($dateStart->format('Y-m-d 23:59:59')),
        ];
    }

    /**
     * Get interval of week
     *
     * @param DateTime $dateStart
     * @param DateTime $dateEnd
     * @param bool $firstInterval
     * @param int $firstDayWeek
     * @return array
     * @throws LocalizedException
     */
    protected function getWeekInterval(DateTime &$dateStart, DateTime $dateEnd, $firstInterval, $firstDayWeek)
    {
        $interval = [];
        //get week in this month
        $interval['period'] = 'W' . $this->weekOfMonth($dateStart, $firstDayWeek) . $dateStart->format('/m/Y');
        $interval['start'] = $this->localeDate->convertConfigTimeToUtc($dateStart->format('Y-m-d 00:00:00'));

        if (strcmp($dateStart->format('m/Y'), $dateEnd->format('m/Y')) == 0 &&
            $this->weekOfMonth($dateStart, $firstDayWeek) == $this->weekOfMonth($dateEnd, $firstDayWeek)) {
            $interval['end'] = $this->localeDate->convertConfigTimeToUtc($dateEnd->format('Y-m-d 23:59:59'));
        } else {
            if ($firstInterval || date('N', $dateStart->getTimestamp()) != $firstDayWeek) {
                $deviationTime = date('N', $dateStart->getTimestamp()) - $firstDayWeek + 1;
                $deviationTime = ($deviationTime >= 0) ? (7 - $deviationTime) : (-$deviationTime);
                $interval['end'] = $this->localeDate->convertConfigTimeToUtc(
                    (new DateTime($dateStart->format('Y-m-d')))
                        ->modify("+" . $deviationTime . " day")->format('Y-m-d 23:59:59')
                );
            } else {
                $interval['end'] = $this->localeDate->convertConfigTimeToUtc(
                    (new DateTime($dateStart->format('Y-m-d')))->modify("+6 day")->format('Y-m-d 23:59:59')
                );
            }
            if (strcmp(
                    substr($interval['end'], 0, 7),
                    substr($this->localeDate->convertConfigTimeToUtc($dateStart), 0, 7)
                )
                != 0) {
                $interval['end'] = $this->localeDate->convertConfigTimeToUtc(
                    (new DateTime($dateStart->format('Y-m-d')))
                        ->modify('last day of this month')->format('Y-m-d 23:59:59')
                );
            }
        }
        $deltaday = ($this->localeDate->date($interval['start']))
                ->diff($this->localeDate->date($interval['end']))->d + 1;
        $dateStart->modify('+' . $deltaday . ' day');
        if ($dateStart->getTimezone()->getName() == "UTC") {
            $dateStart = $this->localeDate->date($dateStart);
        }
        return $interval;
    }

    /**
     * Get number of week in year
     *
     * @param DateTime $dateTime
     * @param int|string $firstDayWeek
     * @return int|string
     * @throws DatetimeException
     */
    protected function weekOfMonth($dateTime, $firstDayWeek)
    {
        if ($dateTime->format('d') == 1) {
            return 1;
        }
        $weekOfYear = date('W', $dateTime->getTimestamp());
        $firstDayOfMonth = date(
            'N',
            date_create($dateTime->format('Y') . '-' . $dateTime->format('m') . '-' . '01')->getTimestamp()
        );
        $checkDayOfWeek = $firstDayWeek - $firstDayOfMonth;
        $dayInWeek = date('N', $dateTime->getTimestamp());
        if ($checkDayOfWeek <= 0) {
            $weekOfYear = $dayInWeek < $firstDayWeek ? ($weekOfYear - 1) : $weekOfYear;
        } else {
            $weekOfYear = $dayInWeek >= $firstDayWeek ? ($weekOfYear + 1) : $weekOfYear;
        }
        //if day 01 is sunday, this day won't be the first week in year
        $firstWeekOfMonth = date(
            'W',
            date_create($dateTime->format('Y') . '-' . $dateTime->format('m') . '-' . '02')->getTimestamp()
        );
        if ($firstDayOfMonth == 7) {
            return $weekOfYear - $firstWeekOfMonth + 2;
        }
        return $weekOfYear - $firstWeekOfMonth + 1;
    }

    /**
     * Get interval for a month
     *
     * @param DateTime $dateStart
     * @param DateTime $dateEnd
     * @param bool $firstInterval
     * @return array
     * @throws LocalizedException
     */
    protected function getMonthInterval(DateTime $dateStart, DateTime $dateEnd, $firstInterval)
    {
        $interval = [];
        $interval['period'] = $dateStart->format('m/Y');
        if ($firstInterval) {
            $interval['start'] = $this->localeDate->convertConfigTimeToUtc($dateStart->format('Y-m-d 00:00:00'));
        } else {
            $interval['start'] = $this->localeDate->convertConfigTimeToUtc($dateStart->format('Y-m-01 00:00:00'));
        }

        if ($dateStart->diff($dateEnd)->m == 0) {
            $interval['end'] = $this->localeDate->convertConfigTimeToUtc(
                $dateStart->setDate(
                    $dateStart->format('Y'),
                    $dateStart->format('m'),
                    $dateEnd->format('d')
                )->format('Y-m-d 23:59:59')
            );
        } else {
            $dateStartUtc = (new DateTime())->createFromFormat('d-m-Y g:i:s', $dateStart->format('d-m-Y 00:00:00'));
            $interval['end'] =
                $this->localeDate->convertConfigTimeToUtc(
                    $dateStart->format('Y-m-' . date('t', $dateStartUtc->getTimestamp()) . ' 23:59:59')
                );
        }

        $dateStart->modify('+1 month');

        if ($dateStart->diff($dateEnd)->m == 0) {
            $dateStart->setDate($dateStart->format('Y'), $dateStart->format('m'), 1);
        }

        return $interval;
    }

    /**
     * Get Interval for a year
     *
     * @param DateTime $dateStart
     * @param DateTime $dateEnd
     * @param bool $firstInterval
     * @return array
     * @throws LocalizedException
     */
    protected function getYearInterval(DateTime $dateStart, DateTime $dateEnd, $firstInterval)
    {
        $interval = [];
        $interval['period'] = $dateStart->format('Y');
        $interval['start'] = $firstInterval
            ? $this->localeDate->convertConfigTimeToUtc($dateStart->format('Y-m-d 00:00:00'))
            : $this->localeDate->convertConfigTimeToUtc($dateStart->format('Y-01-01 00:00:00'));

        $interval['end'] = $dateStart->format('Y') === $dateEnd->format('Y')
            ? $this->localeDate->convertConfigTimeToUtc(
                $dateStart->setDate($dateStart->format('Y'), $dateEnd->format('m'), $dateEnd->format('d'))
                    ->format('Y-m-d 23:59:59')
            )
            : $this->localeDate->convertConfigTimeToUtc($dateStart->format('Y-12-31 23:59:59'));
        $dateStart->modify('+1 year');

        if ($dateStart->diff($dateEnd)->y == 0) {
            $dateStart->setDate($dateStart->format('Y'), 1, 1);
        }

        return $interval;
    }

    /**
     * Get page size
     *
     * @codeCoverageIgnore
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Set page size
     *
     * @codeCoverageIgnore
     *
     * @param int $size
     * @return $this
     */
    public function setPageSize($size)
    {
        $this->pageSize = $size;
        return $this;
    }

    /**
     * Get report for some interval
     *
     * @param int|string $fromDate
     * @param int|string $toDate
     * @return AbstractCollection
     */
    protected function getReport($fromDate, $toDate)
    {
        $reportResource = $this->earnCollectionFactory->create();
        return $reportResource->filterEarnReport($fromDate, $toDate, $this->customerGroup, $this->website);
    }
}
