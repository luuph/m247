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
 * Webkul Walletsystem Wallet Credit Amount Interface
 */
interface WalletCreditAmountInterface
{
    public const  ENTITY_ID = 'entity_id';
    public const  AMOUNT = 'amount';
    public const  ORDER_ID = 'order_id';
    public const  STATUS = 'status';
    public const  REFUND_AMOUNT = 'refund_amount';

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
     * @return \Webkul\Walletsystem\Api\Data\WalletCreditAmountInterface
     */
    public function setEntityId($id);

    /**
     * Get Amount
     *
     * @return int|null
     */
    public function getAmount();

    /**
     * Set Amount
     *
     * @param int $amount
     * @return \Webkul\Walletsystem\Api\Data\WalletCreditAmountInterface
     */
    public function setAmount($amount);

    /**
     * Get Order ID
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Set Order ID
     *
     * @param int $ids
     * @return \Webkul\Walletsystem\Api\Data\WalletCreditAmountInterface
     */
    public function setOrderId($ids);

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param int $status
     * @return \Webkul\Walletsystem\Api\Data\WalletCreditAmountInterface
     */
    public function setStatus($status);

    /**
     * Get Refund Amount
     *
     * @return int|null
     */
    public function getRefundAmount();

    /**
     * Set Refund Amount
     *
     * @param int $refund_amount
     * @return \Webkul\Walletsystem\Api\Data\WalletCreditAmountInterface
     */
    public function setRefundAmount($refund_amount);
}
