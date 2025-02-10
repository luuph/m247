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

namespace Bss\RewardPoint\Block\Adminhtml\EarnReport;

use Bss\RewardPoint\Helper\Data as HelperData;
use Bss\RewardPoint\Model\CurrencyHeader;
use Bss\RewardPoint\Model\DataCollection;
use Laminas\Db\Sql\Ddl\Column\Datetime;
use Laminas\Validator\Date;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\Parameters;
use Magento\Framework\Url\DecoderInterface;

class Filter extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * Should Store Switcher block be visible
     *
     * @var bool
     */
    protected $_storeSwitcherVisibility = true;

    /**
     * Should Date Filter block be visible
     *
     * @var bool
     */
    protected $_dateFilterVisibility = true;

    /**
     * Filters array
     *
     * @var array
     */
    protected $_filters = [];

    /**
     * Default filters values
     *
     * @var array
     */
    protected $_defaultFilters = [
        'report_from' => '',
        'report_to' => '',
        'report_period' => 'overall',
        'report_customerGroup' => -1,
        'report_website' => 0
    ];

    /**
     * Sub-report rows count
     *
     * @var int
     */
    protected $_subReportSize = 5;

    /**
     * Errors messages aggregated array
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Block template file name
     *
     * @var string
     */
    protected $_template = 'Bss_RewardPoint::earnreport/filter.phtml';

    /**
     * Filter values array
     *
     * @var array
     */
    protected $_filterValues;

    /**
     * @var DataCollection
     */
    protected $dataCollection;

    /**
     * @var HelperData
     */
    protected $dataHelper;

    /**
     * @var DecoderInterface
     */
    private $urlDecoder;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @var CurrencyHeader
     */
    protected $currencyHeader;

    /**
     * @param CurrencyHeader $currencyHeader
     * @param DataCollection $dataCollection
     * @param HelperData $dataHelper
     * @param Context $context
     * @param Data $backendHelper
     * @param array $data
     * @param DecoderInterface|null $urlDecoder
     * @param Parameters|null $parameters
     */
    public function __construct(
        \Bss\RewardPoint\Model\CurrencyHeader $currencyHeader,
        \Bss\RewardPoint\Model\DataCollection $dataCollection,
        HelperData                            $dataHelper,
        Context                               $context,
        Data                                  $backendHelper,
        array                                 $data = [],
        DecoderInterface                      $urlDecoder = null,
        Parameters                            $parameters = null
    ) {
        $this->currencyHeader = $currencyHeader;
        $this->dataCollection = $dataCollection;
        $this->dataHelper = $dataHelper;
        $this->urlDecoder = $urlDecoder ?? ObjectManager::getInstance()->get(
            DecoderInterface::class
        );

        $this->parameters = $parameters ?? ObjectManager::getInstance()->get(
            Parameters::class
        );

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws LocalizedException
     */
    protected function _prepareCollection()
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);

        if (null === $filter) {
            $filter = $this->_defaultFilters;
        }
        $collection = $this->getCollection();

        $emptyFromPeriod = $emptyToPeriod = false;
        $filterValue = [];
        if (is_string($filter)) {
            // this is a replacement for base64_decode()
            $filter = $this->urlDecoder->decode($filter);

            // this is a replacement for parse_str()
            $this->parameters->fromString(urldecode($filter));
            $data = $this->parameters->toArray();
            $filterValue = $data;
        } elseif ($filter && is_array($filter)) {
            $filterValue = $filter;
        } elseif (0 !== count($this->_defaultFilters)) {
            $this->_setFilterValues($this->_defaultFilters);
        }
        if ($filterValue) {
            if (!(array_key_exists('report_from', $filterValue) && $filterValue['report_from'])) {
                $emptyFromPeriod = true;
                $filterValue['report_from'] = $this->_localeDate->formatDateTime(
                    $collection->getDateStart() ?? (new \DateTime()),
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::NONE
                );
            }
            if (!(array_key_exists('report_to', $filterValue) && $filterValue['report_to'])) {
                $emptyToPeriod = true;
                $filterValue['report_to'] = $this->_localeDate->formatDateTime(
                    (new \DateTime()),
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::NONE
                );
            }
            $this->_setFilterValues($filterValue);
        }
        $collection = $this->getCollection();
        if ($collection) {
            $collection->setPeriod($this->getFilter('report_period'));

            if ($this->getFilter('report_from') && $this->getFilter('report_to')) {
                /**
                 * Validate from and to date
                 */
                try {
                    //if intervals are more than 100 records, limit
                    $this->limitRecord($this->getFilter('report_from'), $this->getFilter('report_to'));
                    $from = $this->_localeDate->date($this->getFilter('report_from'), null, true, false);
                    $to = $this->_localeDate->date($this->getFilter('report_to'), null, true, false);
                    $collection->setInterval($from, $to);
                    $collection->setCustomerGroup($this->getFilter('report_customerGroup'));
                    $collection->setWebsiteId($this->getFilter('report_website'));
                    if (array_key_exists('report_currency', $filterValue)) {
                        $collection->setCurrency($this->getFilter('report_currency'));
                        $this->currencyHeader->currency = $filterValue['report_currency'];
                    }

                    $this->setFilter('report_from', $emptyFromPeriod ? '' :
                        $this->getFilter('report_from'));
                    $this->setFilter('report_to', $emptyToPeriod ? '' :
                        $this->getFilter('report_to'));
                } catch (\Exception $e) {
                    $this->_errors[] = __('Invalid date specified');
                }
            }

            if ($this->getSubReportSize() !== null) {
                $collection->setPageSize($this->getSubReportSize());
            }

            $this->_eventManager->dispatch(
                'adminhtml_widget_grid_filter_rewardpoint_collection',
                ['collection' => $this->getCollection(), 'filter_values' => $this->_filterValues]
            );
        }
        $this->dataCollection->dataCollection = $collection;
        return $this;
    }

    /**
     * If range time to filter is too long, limit from time
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    protected function limitRecord($from, $to)
    {
        $from = $this->_localeDate->date($from, null, true, false);
        $to = $this->_localeDate->date($to, null, true, false);
        switch ($this->getFilter('report_period')) {
            case 'day':
                if ((int)date_diff($from, $to)->format('%a') > 100) {
                    $this->setLimitTime($to, '-100 day');
                }
                break;
            case 'week':
                if (round((int)date_diff($from, $to)->format('%a') / 7) > 100) {
                    $this->setLimitTime($to, '-700 day');
                }
                break;
            case 'month':
                if (round((int)date_diff($from, $to)->format('%a') / 30) > 100) {
                    $this->setLimitTime($to, '-100 month');
                }
                break;
            case 'year':
                if ((int)date_diff($from, $to)->format('%y') > 100) {
                    $this->setLimitTime($to, '-100 year');
                }
                break;
            default:
                break;
        }
    }

    /**
     * Set from for 100 record from to
     *
     * @param Datetime $to
     * @param string $diffTime
     * @return void
     */
    protected function setLimitTime($to, $diffTime)
    {
        $this->setFilter(
            'report_from',
            $this->_localeDate->formatDateTime(
                $to->modify($diffTime),
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::NONE
            )
        );
    }

    /**
     * Return visibility of store switcher
     *
     * Same 99% core
     *
     * @codeCoverageIgnore
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getStoreSwitcherVisibility()
    {
        return $this->_storeSwitcherVisibility;
    }

    /**
     * Set visibility of store switcher
     *
     * Same 99% core
     *
     * @param bool $visible
     * @codeCoverageIgnore
     * @return void
     */
    public function setStoreSwitcherVisibility($visible = true)
    {
        $this->_storeSwitcherVisibility = $visible;
    }

    /**
     * Return store switcher html
     *
     * Same 99% core
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

    /**
     * Return visibility of date filter
     *
     * Same 99% core
     *
     * @codeCoverageIgnore
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDateFilterVisibility()
    {
        return $this->_dateFilterVisibility;
    }

    /**
     * Set visibility of date filter
     *
     * Same 99% core
     *
     * @param bool $visible
     * @return void
     * @codeCoverageIgnore
     */
    public function setDateFilterVisibility($visible = true)
    {
        $this->_dateFilterVisibility = $visible;
    }

    /**
     * Return date filter html
     *
     * Same 99% core
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getDateFilterHtml()
    {
        return $this->getChildHtml('date_filter');
    }

    /**
     * Get periods
     *
     * Same 99% core
     *
     * @return mixed
     */
    public function getPeriods()
    {
        return $this->getCollection()->getPeriods();
    }

    /**
     * Get customer group
     *
     * @return mixed
     */
    public function getCustomerGroups()
    {
        return $this->getCollection()->getCustomerGroup();
    }

    /**
     * Get list website
     *
     * @return mixed
     */
    public function getAllWebsite()
    {
        return $this->getCollection()->getAllWebsite();
    }

    /**
     * Get date format according the locale
     *
     * Same 99% core
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
    }

    /**
     * Retrieve errors
     *
     * Same 99% core
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Set filter values
     *
     * Same 99% core
     *
     * @param array $data
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _setFilterValues($data)
    {
        foreach ($data as $name => $value) {
            $this->setFilter($name, $data[$name]);
        }
        return $this;
    }

    /**
     * Set filter
     *
     * Same 99% core
     *
     * @param string $name
     * @param string $value
     * @return void
     * @codeCoverageIgnore
     */
    public function setFilter($name, $value)
    {
        if ($name) {
            $this->_filters[$name] = $value;
        }
    }

    /**
     * Get filter by key
     *
     * Same 99% core
     *
     * @param string $name
     * @return string
     */
    public function getFilter($name)
    {
        if (isset($this->_filters[$name])) {
            return $this->_filters[$name];
        } else {
            return $this->getRequest()->getParam($name) ? $this->escapeHtml($this->getRequest()->getParam($name)) : '';
        }
    }

    /**
     * Return sub-report rows count
     *
     * Same 99% core
     *
     * @codeCoverageIgnore
     * @return int
     */
    public function getSubReportSize()
    {
        return $this->_subReportSize;
    }

    /**
     * Set sub-report rows count
     *
     * Same 99% core
     *
     * @param int $size
     * @return void
     * @codeCoverageIgnore
     */
    public function setSubReportSize($size)
    {
        $this->_subReportSize = $size;
    }

    /**
     * Prepare grid filter buttons
     *
     * Same 99% core
     *
     * @return void
     */
    protected function _prepareFilterButtons()
    {
        $this->addChild(
            'refresh_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Refresh'), 'onclick' => "{$this->getJsObjectName()}.doFilter();", 'class' => 'task']
        );
    }

    /**
     * Return refresh button html
     *
     * Same 99% core
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getRefreshButtonHtml()
    {
        return $this->getChildHtml('refresh_button');
    }
}
