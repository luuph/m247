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
namespace Bss\CustomerApproval\Api;

/**
 * Customer Approval Management
 *
 * @api
 * @since 100.0.0
 */
interface CustomerApprovalManagementInterface
{

    /**
     * Get module configs
     *
     * @param int|null $storeViewId
     * @return array
     */
    public function getConfig($storeViewId = null);

    /**
     * Get value customer status
     *
     * @return array
     */
    public function getValueCustomerStatus();
}
