<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Controller\Adminhtml\Report;

use Amasty\RmaReports\Api\Data\ReportSettingInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Reports
 */
class SettingsSave extends \Amasty\RmaReports\Controller\Adminhtml\AbstractReport
{
    /**
     * @var \Amasty\RmaReports\Model\ReportSetting\ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\RmaReports\Model\ReportSetting\ResourceModel\ReportSetting
     */
    private $reportSettingResource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\RmaReports\Model\ReportSetting\ResourceModel\CollectionFactory $collectionFactory,
        \Amasty\RmaReports\Model\ReportSetting\ResourceModel\ReportSetting $reportSettingResource
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->reportSettingResource = $reportSettingResource;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $reasonId = (int)$this->_request->getParam('reason_id');
        $resolutionId = (int)$this->_request->getParam('resolution_id');
        $adminId = (int)$this->_auth->getUser()->getId();

        if ($reasonId && $resolutionId && $adminId) {
            try {
                /** @var \Amasty\RmaReports\Model\ReportSetting\ReportSetting $reportSetting */
                $reportSetting = $this->collectionFactory->create()->getSettingByAdminId($adminId);
                $reportSetting->addData(
                    [
                        ReportSettingInterface::ADMIN_ID      => $adminId,
                        ReportSettingInterface::REASON_ID     => $reasonId,
                        ReportSettingInterface::RESOLUTION_ID => $resolutionId
                    ]
                );
                $this->reportSettingResource->save($reportSetting);
            } catch (LocalizedException $e) {
                null;
            }
        }
    }
}
