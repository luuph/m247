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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Plugin\Model\ResourceModel\Grid;

class Collection
{
    /**
     * Before Add field filter to collection
     *
     * @param \Magento\Framework\Data\Collection\AbstractDb $subject
     * @param string|array $field
     * @param null|string|array $condition
     * @return array
     */
    public function beforeAddFieldToFilter(\Magento\Framework\Data\Collection\AbstractDb $subject, $field, $condition = null)
    {
        if ($field == 'sales_rep') {
            $field = 'admin_user.' . 'username';
        }
        if ($field == 'is_company_account') {
            $field = 'bss_is_company_account';
        }
        return [$field, $condition];
    }
}
