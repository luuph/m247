<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerApproval
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerApproval\Model;

use Bss\CustomerApproval\Api\CustomerApprovalManagementInterface;

/**
 * Class CustomerApprovalManagement
 *
 * @package Bss\CustomerApproval\Model
 */
class CustomerApprovalManagement implements CustomerApprovalManagementInterface
{

    /**
     * @var \Bss\CustomerApproval\Model\Config\Source\CustomerStatus
     */
    protected $customerStatus;

    /**
     * @var \Bss\CustomerApproval\Helper\Data
     */
    protected $helperData;

    /**
     * CustomerApprovalManagement constructor.
     * @param \Bss\CustomerApproval\Model\Config\Source\CustomerStatus $customerStatus
     * @param \Bss\CustomerApproval\Helper\Data $helperData
     */
    public function __construct(
        \Bss\CustomerApproval\Model\Config\Source\CustomerStatus $customerStatus,
        \Bss\CustomerApproval\Helper\Data $helperData
    ) {
        $this->customerStatus = $customerStatus;
        $this->helperData = $helperData;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($storeViewId = null)
    {
        $websiteId  = $this->helperData->getWebsiteId($storeViewId);
        return [
            "module_configs" => [
            "enable" => $this->helperData->isEnable($websiteId),
            "auto_approval" => $this->helperData->isAutoApproval($storeViewId),
            "pending_message" => $this->helperData->getPendingMess($storeViewId),
            "disapprove_message" => $this->helperData->getDisapproveMess($storeViewId)
                ]
        ];

    }

    /**
     * @inheritDoc
     */
    public function getValueCustomerStatus()
    {
        return $this->customerStatus->getAllOptions();
    }
}
