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
namespace Bss\RewardPoint\Model;

use Magento\Framework\Model\AbstractExtensibleModel;

class UpdateItemDetails extends AbstractExtensibleModel implements \Bss\RewardPoint\Api\Data\UpdateItemDetailsInterface
{
    /**
     * Get Totals of quote
     *
     * @return \Magento\Quote\Api\Data\TotalsInterface|mixed|null
     */
    public function getTotals()
    {
        return $this->getData(self::TOTALS);
    }

    /**
     * Set Totals
     *
     * @param \Magento\Quote\Api\Data\TotalsInterface $totals
     * @return UpdateItemDetails
     */
    public function setTotals($totals)
    {
        return $this->setData(self::TOTALS, $totals);
    }
}
