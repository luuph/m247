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
 * @package    Bss_AdminActionLog
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdminActionLog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class VisitDetail extends AbstractDb
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_admin_visit_detail_log', 'id');
    }
}
