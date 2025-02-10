<?php
namespace Bss\RewardPoint\Api\Data;

interface EarnPointInterface
{
    /**
     * Constants defined for keys of array, makes typos less likely
     */
    public const STATUS = 'status';

    public const EARN_POINT = 'earn_point';

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param boolean $status
     * @return boolean
     */
    public function setStatus($status);

    /**
     * Get earn point
     *
     * @return float
     */
    public function getEarnPoint();

    /**
     * Set earn point
     *
     * @param int $point
     * @return float
     */
    public function setEarnPoint($point);
}
