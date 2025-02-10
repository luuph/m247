<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Api\Data;

interface ReportSettingInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const SETTING_ID = 'setting_id';
    public const ADMIN_ID = 'admin_id';
    public const RESOLUTION_ID = 'resolution_id';
    public const REASON_ID = 'reason_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getSettingId();

    /**
     * @param int $id
     *
     * @return \Amasty\RmaReports\Api\Data\ReportSettingsInterface
     */
    public function setSettingId($id);

    /**
     * @return int
     */
    public function getAdminId();

    /**
     * @param int $id
     *
     * @return \Amasty\RmaReports\Api\Data\ReportSettingsInterface
     */
    public function setAdminId($id);

    /**
     * @return int
     */
    public function getResolutionId();

    /**
     * @param int $id
     *
     * @return \Amasty\RmaReports\Api\Data\ReportSettingsInterface
     */
    public function setResolutionId($id);

    /**
     * @return int
     */
    public function getReasonId();

    /**
     * @param int $id
     *
     * @return \Amasty\RmaReports\Api\Data\ReportSettingsInterface
     */
    public function setReasonId($id);
}
