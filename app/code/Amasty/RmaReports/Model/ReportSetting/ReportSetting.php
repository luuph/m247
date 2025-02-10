<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */


namespace Amasty\RmaReports\Model\ReportSetting;

use Amasty\RmaReports\Api\Data\ReportSettingInterface;
use Magento\Framework\Model\AbstractModel;

class ReportSetting extends AbstractModel implements ReportSettingInterface
{
    public function _construct()
    {
        $this->_init(\Amasty\RmaReports\Model\ReportSetting\ResourceModel\ReportSetting::class);
        $this->setIdFieldName(ReportSettingInterface::SETTING_ID);
    }

    /**
     * @inheritDoc
     */
    public function getSettingId()
    {
        return (int)$this->_getData(ReportSettingInterface::SETTING_ID);
    }

    /**
     * @inheritDoc
     */
    public function setSettingId($id)
    {
        return $this->setData(ReportSettingInterface::SETTING_ID, (int)$id);
    }

    /**
     * @inheritDoc
     */
    public function getAdminId()
    {
        return (int)$this->_getData(ReportSettingInterface::ADMIN_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAdminId($id)
    {
        return $this->setData(ReportSettingInterface::ADMIN_ID, (int)$id);
    }

    /**
     * @inheritDoc
     */
    public function getResolutionId()
    {
        return (int)$this->_getData(ReportSettingInterface::RESOLUTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setResolutionId($id)
    {
        return $this->setData(ReportSettingInterface::RESOLUTION_ID, (int)$id);
    }

    /**
     * @inheritDoc
     */
    public function getReasonId()
    {
        return (int)$this->_getData(ReportSettingInterface::REASON_ID);
    }

    /**
     * @inheritDoc
     */
    public function setReasonId($id)
    {
        return $this->setData(ReportSettingInterface::REASON_ID, (int)$id);
    }
}
