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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2025 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Plugin;

use Bss\B2bRegistration\Helper\Data;
use Bss\B2bRegistration\Model\Config\Source\CustomerAttribute;
use Magento\Customer\Model\Backend\Customer;

class SaveApprovalMassAction
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * @param Data $data
     */
    public function __construct(
        \Bss\B2bRegistration\Helper\Data $data
    ) {
        $this->data = $data;
    }

    /**
     * @param Customer $subject
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return \Magento\Customer\Api\Data\CustomerInterface[]
     * @throws \Zend_Db_Statement_Exception
     */
    public function beforeUpdateData(
        \Magento\Customer\Model\Backend\Customer $subject, $customer
    ) {
        $customerApproval = $customer->getCustomAttribute('activasion_status');

        if ($customerApproval && $customerApproval->getValue() != $this->data->getApproveValue()) {
            $b2bApproval = $customer->getCustomAttribute('b2b_activasion_status');
            if ($b2bApproval && $b2bApproval->getValue() == CustomerAttribute::B2B_APPROVAL) {
                $customer->setCustomAttribute('activasion_status', $this->data->getApproveValue());
            }
        }
        return [$customer];
    }
}
