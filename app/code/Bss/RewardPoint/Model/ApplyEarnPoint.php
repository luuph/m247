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

class ApplyEarnPoint extends AbstractExtensibleModel implements \Bss\RewardPoint\Api\Data\EarnPointInterface
{
    /**
     * Get Earn point
     *
     * @return float
     */
    public function getEarnPoint()
    {
        return $this->getData(self::EARN_POINT);
    }

    /**
     * Set Earn point
     *
     * @param float $point
     * @return ApplyEarnPoint
     */
    public function setEarnPoint($point)
    {
        return $this->setData(self::EARN_POINT, $point);
    }

    /**
     * Get Status
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Status
     *
     * @param bool $status
     * @return bool|ApplyEarnPoint
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
