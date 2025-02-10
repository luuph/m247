<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Controller\Adminhtml\Customer;

use Bss\CompanyAccount\Model\Config\Source\CompanyAccountValue;

/**
 * Class MassApprovedCompanyAccount
 *
 * @package Bss\CompanyAccount\Controller\Adminhtml\System\Config
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class MassDisapprovedCompanyAccount extends MassActionCompanyAccountAbstract
{
    /**
     * Set status value for action
     *
     * @return int
     */
    protected function setCompanyStatusVal()
    {
        return $this->statusValue = CompanyAccountValue::IS_NORMAL_ACCOUNT;
    }
}
