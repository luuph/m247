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
 * @package    Bss_GuestToCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GuestToCustomer\Plugin\Model\Customer;

use Bss\GuestToCustomer\Helper\ConfigAdmin;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Customer\Model\AccountManagement as AccountManagementCore;

class AccountManagement
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var ConfigAdmin
     */
    private $configAdmin;

    /**
     * Constructor
     *
     * @param ConfigAdmin $configAdmin
     */
    public function __construct(
        ConfigAdmin $configAdmin,
        AuthorizationInterface $authorization
    ) {
        $this->configAdmin = $configAdmin;
        $this->authorization = $authorization;
    }

    /**
     * Compatible magento 245 with guest to customer module
     *
     * @param \Magento\Customer\Model\AccountManagement $subject
     * @param CustomerInterface $customer
     * @return void
     */
    public function beforeCreateAccountWithPasswordHash($subject, $customer)
    {
        if ($this->configAdmin->getConfigEnableModule()
            && !$this->authorization->isAllowed(AccountManagementCore::ADMIN_RESOURCE)) {
            $groupId = $this->configAdmin->getConfigCustomerGroup();
            $customer->setGroupId($groupId);
        }
    }
}
