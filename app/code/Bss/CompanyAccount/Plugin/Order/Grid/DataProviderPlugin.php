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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Plugin\Order\Grid;

use Magento\Framework\Api\Filter;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class DataProviderPlugin
{
    /**
     * Set field main_table.created_at to filter
     *
     * @param DataProvider $subject
     * @param Filter $filter
     * @return void
     */
    public function beforeAddFilter(
        DataProvider $subject,
        Filter       $filter
    ) {
        if ($subject->getName() === "sales_order_grid_data_source" && $filter->getField() == 'created_at') {
            $filter->setField("main_table." . $filter->getField());
        }
    }
}
