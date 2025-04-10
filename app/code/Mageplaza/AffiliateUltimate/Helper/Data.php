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
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliateUltimate\Helper;

use Magento\Framework\Api\SortOrder;
use Mageplaza\AffiliatePro\Helper\Data as AffiliateProHelper;

/**
 * Class Data
 * @package Mageplaza\AffiliateUltimate\Helper
 */
class Data extends AffiliateProHelper
{
    /**
     * @return bool
     */
    public function canUseStoreSwitcherLayoutByMpReports()
    {
        if ($this->isModuleOutputEnabled('Mageplaza_Reports')) {
            return $this->getMpReportHelper()->isEnabled();
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getMpReportHelper()
    {
        return $this->objectManager->get('\Mageplaza\Reports\Helper\Data');
    }

    /**
     * @return array
     */
    public function getFilterDate()
    {
        $filterDate = [];
        if ($this->canUseStoreSwitcherLayoutByMpReports()) {
            $mpReportHelper = $this->getMpReportHelper();
            $dateRange      = $mpReportHelper->getDateRange();
            $filterDate     = $mpReportHelper->getDateTimeRangeFormat($dateRange[0], $dateRange[1], 1);
        }

        return $filterDate;
    }

    /**
     * @param $searchCriteria
     * @param $searchResult
     *
     * @return mixed
     */
    public function processGetList($searchCriteria, $searchResult)
    {
        if ($this->versionCompare('2.2.0')) {
            $collectionProcessor = $this->objectManager->get('\Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface');
            $joinProcessor       = $this->objectManager->get('\Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface');
            $collectionProcessor->process($searchCriteria, $searchResult);
            $joinProcessor->process($searchResult);
        } else {
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                $this->addFilterGroupToCollection($filterGroup, $searchResult);
            }

            $sortOrders = $searchCriteria->getSortOrders();
            if ($sortOrders === null) {
                $sortOrders = [];
            }
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $field = $sortOrder->getField();
                $searchResult->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }

            $searchResult->setCurPage($searchCriteria->getCurrentPage());
            $searchResult->setPageSize($searchCriteria->getPageSize());
        }

        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getStatisticsTotalCommissionOnHold($storeId = null)
    {
        return $this->getModuleConfig('commission/statistics_total_commission_on_hold', $storeId);
    }
}
