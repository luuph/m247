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
namespace Bss\CustomerApproval\Helper;

use Bss\CustomerApproval\Model\ResourceModel\Options;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package Bss\CustomerApproval\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Options $optionModel
     */
    protected $optionModel;

    /**
     * Data constructor.
     * @param Context $context
     * @param Options $optionModel
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Context $context,
        Options $optionModel
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
        $this->optionModel = $optionModel;
    }

    /**
     * Check enable/disable module
     *
     * Get Enable|Disable
     * @param int|null $websiteId
     * @return bool
     */
    public function isEnable($websiteId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'customer_approval/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * @return true|false
     */
    public function isEnableAdminEmail()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_approval/admin_notification/admin_notification_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check enable/disable customer email
     *
     * @param null|int $storeId
     * @return true|false
     */
    public function isEnableCustomerEmail($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'customer_approval/email_setting/customer_email_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is auto approval
     *
     * @param null|int $storeViewId
     * @return true|false
     */
    public function isAutoApproval($storeViewId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'customer_approval/general/auto_approval',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeViewId
        );
    }

    /**
     * Get pending message
     *
     * @param int|null $storeViewId
     * @return string
     */
    public function getPendingMess($storeViewId = null)
    {
        $pendingMess= $this->scopeConfig->getValue(
            'customer_approval/general/pending_message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeViewId
        );
        return $pendingMess;
    }

    /**
     * @return string
     */
    public function getAdminEmailTemplate()
    {
        $emailTemplate= $this->scopeConfig->getValue(
            'customer_approval/admin_notification/admin_email_templates',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $emailTemplate;
    }
    /**
     * @param null $storeId
     * @return string
     */
    public function getCustomerApproveEmailTemplate($storeId = null)
    {
        $customerApproveEmailTemplate= $this->scopeConfig->getValue(
            'customer_approval/email_setting/customer_approve_templates',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $customerApproveEmailTemplate;
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getCustomerRegistrationSuccessEmailTemplates($storeId = null)
    {
        $customerRegistrationSuccessEmailTemplates= $this->scopeConfig->getValue(
            'customer_approval/email_setting/customer_registration_success_email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $customerRegistrationSuccessEmailTemplates;
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getCustomerDisapproveEmailTemplate($storeId = null)
    {
        $customerDisapproveEmailTemplate= $this->scopeConfig->getValue(
            'customer_approval/email_setting/customer_disapprove_templates',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $customerDisapproveEmailTemplate;
    }

    /**
     * @return string
     */
    public function getAdminEmailSender()
    {
        $emailSender= $this->scopeConfig->getValue(
            'customer_approval/admin_notification/admin_email_sender',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $emailSender;
    }

    /**
     * @return string
     */
    public function getAdminEmail()
    {
        $emailAdmin= $this->scopeConfig->getValue(
            'customer_approval/admin_notification/admin_recipeints',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $emailAdmin;
    }

    /**
     * @return string
     */
    public function getCustomerEmailSender()
    {
        $customerEmailSender= $this->scopeConfig->getValue(
            'customer_approval/email_setting/customer_email_sender',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $customerEmailSender;
    }

    /**
     * Get disapprove message
     *
     * @param int|null $storeViewId
     * @return string
     */
    public function getDisapproveMess($storeViewId = null)
    {
        $pendingMess= $this->scopeConfig->getValue(
            'customer_approval/general/disapprove_message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeViewId
        );
        return $pendingMess;
    }

    /**
     * @return int
     * @throws \Zend_Db_Statement_Exception
     */
    public function getPendingValue()
    {
        $pending = (int) $this->optionModel->getStatusValue('Pending')['option_id'];
        return $pending;
    }

    /**
     * @return int
     * @throws \Zend_Db_Statement_Exception
     */
    public function getApproveValue()
    {
        $approve = (int) $this->optionModel->getStatusValue('Approved')['option_id'];
        return $approve;
    }

    /**
     * @return int
     * @throws \Zend_Db_Statement_Exception
     */
    public function getDisApproveValue()
    {
        $disapprove = (int) $this->optionModel->getStatusValue('Disapproved')['option_id'];
        return $disapprove;
    }

    /**
     * Get website ID by store view id
     *
     * @param int $storeViewId
     * @return int|null
     */
    public function getWebsiteId($storeViewId)
    {
        try {
            return $this->storeManager->getStore($storeViewId)->getWebsiteId();
        } catch (\Exception $exception) {
            $this->_logger->critical($exception->getMessage());
            return null;
        }

    }
}
