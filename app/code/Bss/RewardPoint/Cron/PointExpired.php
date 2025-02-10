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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Cron;

use Bss\RewardPoint\Model\TransactionFactory;

/**
 * Class Point Expired
 *
 * Bss\RewardPoint\Cron
 */
class PointExpired
{
    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * PointExpired constructor.
     * @param \Bss\RewardPoint\Model\TransactionFactory $transactionFactory
     */
    public function __construct(
        \Bss\RewardPoint\Model\TransactionFactory $transactionFactory
    ) {
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        $this->transactionFactory->create()->updatePointExpired();
        return $this;
    }
}
