<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Model\Report\DataProvider;

use Amasty\Rma\Api\Data\RequestItemInterface;
use Amasty\RmaReports\Model\Report\ResourceModel\ReportCollectionsGenerator;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DetailsListing extends AbstractDataProvider
{
    public function __construct(
        ReportCollectionsGenerator $collections,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collections->getDetailsCollection();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritDoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        switch ($filter->getField()) {
            case 'request_id':
                $filter->setField('main_table.' . \Amasty\Rma\Api\Data\RequestInterface::REQUEST_ID);
                parent::addFilter($filter);
                break;
            case 'total':
                $filter->setField(new \Zend_Db_Expr(
                    'CAST(SUM(soi.price * rmai.' . RequestItemInterface::REQUEST_QTY . ') AS DECIMAL(10,2))'
                ));
                switch ($filter->getConditionType()) {
                    case "gteq":
                        $this->collection->getSelect()->having($filter->getField() . ' >= ?', $filter->getValue());
                        break;
                    case "lteq":
                        $this->collection->getSelect()->having($filter->getField() . ' <= ?', $filter->getValue());
                        break;
                }
                break;
            case 'lead_time':
                $filter->setField(new \Zend_Db_Expr(
                    'DATEDIFF(main_table.' . \Amasty\Rma\Api\Data\RequestInterface::MODIFIED_AT
                    . ', main_table.' . \Amasty\Rma\Api\Data\RequestInterface::CREATED_AT . ')'
                ));
                parent::addFilter($filter);
                break;
            case 'resolutions':
                $filter->setField('rmai.' . RequestItemInterface::RESOLUTION_ID);
                parent::addFilter($filter);
                break;
            case 'conditions':
                $filter->setField('rmai.' . RequestItemInterface::CONDITION_ID);
                parent::addFilter($filter);
                break;
            case 'reasons':
                $filter->setField('rmai.' . RequestItemInterface::REASON_ID);
                parent::addFilter($filter);
                break;
            case 'skus':
                $filter->setField(new \Zend_Db_Expr(
                    'GROUP_CONCAT( soi.sku SEPARATOR "<br/>")'
                ));
                $this->collection->getSelect()->having($filter->getField() . ' like ?', $filter->getValue());
                break;
            default:
                parent::addFilter($filter);
        }
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $data = parent::getData();
        $data['totals']['total'] = 0;

        foreach ($data['items'] as &$item) {
            $data['totals']['total'] += $item['total'];

            if ($item['rating'] == 0) {
                $item['rating'] = null;
            }
        }

        return $data;
    }
}
