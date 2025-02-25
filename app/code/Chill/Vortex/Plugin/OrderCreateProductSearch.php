<?php

namespace Chill\Vortex\Plugin;

use Magento\Sales\Ui\DataProvider\Order\Create\Product\SearchDataProvider;
use Magento\Framework\Api\Filter;

class OrderCreateProductSearch
{
    public function afterGetSearchCriteria(SearchDataProvider $subject, $searchCriteria)
    {
        $filters = $searchCriteria->getFilterGroups();

        // Thêm bộ lọc tìm kiếm fulltext
        $filter = new Filter();
        $filter->setField('fulltext');
        $filter->setConditionType('like');
        $filter->setValue('%'.trim($_GET['fulltext'] ?? '').'%');

        $filterGroup = $subject->getFilterGroupFactory()->create();
        $filterGroup->setFilters([$filter]);
        $filters[] = $filterGroup;

        $searchCriteria->setFilterGroups($filters);
        return $searchCriteria;
    }
}
