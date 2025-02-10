<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Model\ReportSetting\ResourceModel;

use Amasty\RmaReports\Api\Data\ReportSettingInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ReportSetting extends AbstractDb
{
    public const TABLE_NAME = 'amasty_rma_reports_setting';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ReportSettingInterface::SETTING_ID);
    }
}
