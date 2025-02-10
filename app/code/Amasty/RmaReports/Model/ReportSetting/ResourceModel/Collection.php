<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Model\ReportSetting\ResourceModel;

use Amasty\RmaReports\Api\Data\ReportSettingInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(
            \Amasty\RmaReports\Model\ReportSetting\ReportSetting::class,
            \Amasty\RmaReports\Model\ReportSetting\ResourceModel\ReportSetting::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param int $adminId
     *
     * @return \Amasty\RmaReports\Model\ReportSetting\ReportSetting
     */
    public function getSettingByAdminId($adminId)
    {
        return $this->addFieldToFilter(ReportSettingInterface::ADMIN_ID, (int)$adminId)->getFirstItem();
    }
}
