<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Api\Data;

/**
 * Webkul Walletsystem Wallet record Interface
 */
interface WalletrecordInterface
{
    public const  ENTITY_ID = 'entity_id';

    public const CUSTOMER_ID = 'customer_id';

    public const TOTAL_AMOUNT = 'total_amount';

    public const REMAINING_AMOUNT = 'remaining_amount';

    public const USED_AMOUNT = 'used_amount';

    public const UPDATED_AT = 'updated_at';

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set Entity ID
     *
     * @param int $id
     * @return \Webkul\Walletsystem\Api\Data\WalletrecordInterface
     */
    public function setEntityId($id);

    /**
     * Get Customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set Customer ID
     *
     * @param int $customer_id
     * @return \Webkul\Walletsystem\Api\Data\WalletrecordInterface
     */
    public function setCustomerId($customer_id);

    /**
     * Get Total Amount
     *
     * @return float|null
     */
    public function getTotalAmount();

    /**
     * Set Total Amount
     *
     * @param float $total_amount
     * @return \Webkul\Walletsystem\Api\Data\WalletrecordInterface
     */
    public function setTotalAmount($total_amount);

    /**
     * Get Remaining Amount
     *
     * @return float|null
     */
    public function getRemainingAmount();

    /**
     * Set Remaining Amount
     *
     * @param float $remaining_amount
     * @return \Webkul\Walletsystem\Api\Data\WalletrecordInterface
     */
    public function setRemainingAmount($remaining_amount);

    /**
     * Get Used Amount
     *
     * @return float|null
     */
    public function getUsedAmount();

    /**
     * Set Used Amount
     *
     * @param float $used_amount
     * @return \Webkul\Walletsystem\Api\Data\WalletrecordInterface
     */
    public function setUsedAmount($used_amount);

    /**
     * Get Updated Time
     *
     * @return date|null
     */
    public function getUpdatedAt();

    /**
     * Set Updated Time
     *
     * @param date $updated_at
     * @return \Webkul\Walletsystem\Api\Data\WalletrecordInterface
     */
    public function setUpdatedAt($updated_at);
}
