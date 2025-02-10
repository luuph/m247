<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Model\Report\DataProvider;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;

/**
 * Class Form
 */
class OverviewForm extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var \Amasty\RmaReports\Model\ReportSetting\ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    private $auth;

    public function __construct(
        \Amasty\RmaReports\Model\ReportSetting\ResourceModel\CollectionFactory $collectionFactory,
        \Magento\Backend\Model\Auth $auth,
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->collectionFactory = $collectionFactory;
        $this->auth = $auth;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = [];

        if ($adminId = (int)$this->auth->getUser()->getId()) {
            $reportSetting = $this->collectionFactory->create()->getSettingByAdminId($adminId);
            $data = [
                null => [
                    'reason' => $reportSetting->getReasonId(),
                    'resolution' => $reportSetting->getResolutionId()
                ]
            ];
        }

        return $data;
    }
}
