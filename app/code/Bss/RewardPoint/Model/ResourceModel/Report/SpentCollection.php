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

use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SpentCollection extends Collection
{
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
                'period' => ""
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
                        if ($item->getTotalSpentPoint() == 0) {
                            $intervalEmpty = true;
                            $emptyInterval->setIsEmpty(true);
                            break;
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
     * Get report from database
     *
     * @param int|string $fromDate
     * @param int|string $toDate
     * @return EarnCollection|AbstractCollection
     * @throws Exception
     */
    protected function getReport($fromDate, $toDate)
    {
        $reportResource = $this->earnCollectionFactory->create();
        return $reportResource->filterSpentReport($fromDate, $toDate, $this->customerGroup, $this->website, $this->currency);
    }
}
