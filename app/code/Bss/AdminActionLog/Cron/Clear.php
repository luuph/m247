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
namespace Bss\AdminActionLog\Cron;

class Clear
{
    /** @var \Bss\AdminActionLog\Model\ResourceModel\ClearLog $clearLog */
    private $clearlog;
    /**
     * @var
     */
    protected $resources;

    /**
     * Clear constructor.
     * @param \Bss\AdminActionLog\Model\ResourceModel\ClearLog $clearlog
     */
    public function __construct(
        \Bss\AdminActionLog\Model\ResourceModel\ClearLog $clearlog
    ) {
        $this->clearlog = $clearlog;
    }

    /**
     * @return $this
     * @throws \Zend_Db_Statement_Exception
     */
    public function execute()
    {
        $this->clearlog->Delete();

        return $this;
    }
}
