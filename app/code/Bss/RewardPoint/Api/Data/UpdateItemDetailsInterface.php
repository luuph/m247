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
namespace Bss\RewardPoint\Api\Data;

/**
 * Interface UpdateItemDetailsInterface
 * @api
 */
interface UpdateItemDetailsInterface
{
    /**
     * Constants defined for keys of array, makes typos less likely
     */

    public const TOTALS = 'totals';

    public const MESSAGE = 'message';

    public const STATUS = 'status';

    /**
     * Get totals
     *
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function getTotals();

    /**
     * Set totals
     *
     * @param \Magento\Quote\Api\Data\TotalsInterface $totals
     * @return \Bss\RewardPoint\Api\Data\UpdateItemDetailsInterface
     */
    public function setTotals($totals);
}
