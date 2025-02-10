<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Plugin\Request;

use Amasty\Rma\Model\Request\ResourceModel\Collection;
use Magento\Framework\Db\Select;

class CollectionPlugin
{
    public function aroundGetSelectCountSql(Collection $subject, \Closure $proceed)
    {
        $countSelect = $proceed();

        if ($subject->getSelect()->getPart(Select::HAVING)) {
            $countSelect->reset();
            $group = $subject->getSelect()->getPart(Select::GROUP);
            $countSelect->from(
                ['main_table' => $subject->getSelect()],
                [new \Zend_Db_Expr("COUNT(DISTINCT " . implode(", ", $group) . ")")]
            );
        }

        return $countSelect;
    }
}
