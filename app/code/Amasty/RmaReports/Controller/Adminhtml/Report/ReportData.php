<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Controller\Adminhtml\Report;

use Amasty\RmaReports\Model\Report\ResourceModel\Stats;
use Amasty\RmaReports\Model\Report\ResourceModel\StatsFactory;
use Amasty\RmaReports\Model\DateProcessor;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Reports
 */
class ReportData extends \Amasty\RmaReports\Controller\Adminhtml\AbstractReport
{
    /**
     * @var StatsFactory
     */
    private $statsResourceFactory;

    /**
     * @var DateProcessor
     */
    private $dateProcessor;

    public function __construct(
        StatsFactory $statsResourceFactory,
        DateProcessor $dateProcessor,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->statsResourceFactory = $statsResourceFactory;
        $this->dateProcessor = $dateProcessor;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        list($dateFrom, $dateTo) = $this->dateProcessor->getFromToDate();

        /** @var Stats $stats */
        $stats = $this->statsResourceFactory->create([
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'namespace' => $this->_request->getParam('namespace'),
            'items' => $this->_request->getParam('items', [])
        ]);
        list($totalRequests, $requestsCount) = $stats->getTotalRequests();
        list($totalPercentage, $returnsPercentage) = $stats->getReturnPercentage();
        list($totalLeadTime, $leadTime) = $stats->getLeadTime();
        list($totalRating, $rating) = $stats->getRating();
        list($totalStoreDelivery, $storeDelivery) = $stats->getStoreDeliver();

        $data = [
            'totalData' => [
                'data' => $totalRequests,
                'requestsCount' => $requestsCount,
            ],
            'percentageData' => [
                'data' => $totalPercentage,
                'returnsPercentage' => $returnsPercentage
            ],
            'leadTimeData' => [
                'data' => $totalLeadTime,
                'leadTime' => $leadTime
            ],
            'ratingData' => [
                'data' => $totalRating,
                'rating' => $rating
            ],
            'storeDeliveryData' => [
                'data' => $totalStoreDelivery,
                'storeDelivery' => $storeDelivery
            ],
            'reasonsData' => $stats->getTopReasons()
        ];

        return $result->setData($data);
    }
}
