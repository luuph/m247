<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Model\Report\DataProvider;

use Amasty\RmaReports\Model\Report\ResourceModel\ReportCollectionsGenerator;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\RequestInterface;

/**
 * Class ItemsListing
 */
class OverviewListing extends AbstractDataProvider
{
    public function __construct(
        ReportCollectionsGenerator $collections,
        RequestInterface $request,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        switch ($request->getParam('namespace')) {
            case 'amrmarep_report_reason_listing':
                $this->collection = $collections
                    ->getReasonItemsCollection((int)$request->getParam('reason_id', 1));
                break;
            case 'amrmarep_report_resolution_listing':
                $this->collection = $collections
                    ->getCustomerItemsCollection((int)$request->getParam('resolution_id', 1));
                break;
        }
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}
