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
 * Webkul Walletsystem Wallet transaction Interface
 */
interface WallettransactionInterface
{
    public const  ENTITY_ID = 'entity_id';

    public const  CUSTOMER_ID = 'customer_id';

    public const  AMOUNT = 'amount';

    public const  STATUS = 'status';

    public const  ACTION = 'action';

    public const  ORDER_ID = 'order_id';

    public const  TRANSACTION_AT = 'transaction_at';

    public const  CURRENCY_CODE = 'currency_code';

    public const  CURR_AMOUNT = 'curr_amount';

    public const  TRANSACTION_NOTE = 'transaction_note';

    public const  SENDER_ID = 'sender_id';

    public const  SENDER_TYPE = 'sender_type';

    public const  BANK_DETAILS = 'bank_details';

    /**
     * Get entity ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set Entity ID
     *
     * @param int $id
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionInterface
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
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionInterface
     */
    public function setCustomerId($customer_id);

    /**
     * Get Total Amount
     *
     * @return float|null
     */
    public function getAmount();

    /**
     * Set Total Amount
     *
     * @param float $amount
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionInterface
     */
    public function setAmount($amount);

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
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionInterface
     */
    public function setStatus($status);

    /**
     * Get Action
     *
     * @return string|null
     */
    public function getAction();

    /**
     * Set Action
     *
     * @param string $action
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionInterface
     */
    public function setAction($action);

    /**
     * Get Order ID
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Set Order ID
     *
     * @param int $order_id
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionInterface
     */
    public function setOrderId($order_id);

    /**
     * Get Transaction Time
     *
     * @return date|null
     */
    public function getTransactionAt();

    /**
     * Set Transaction Time
     *
     * @param date $transaction_at
     * @return \Webkul\Walletsystem\Api\Data\WalletrecordInterface
     */
    public function setTransactionAt($transaction_at);

    /**
     * Get Currency Code
     *
     * @return string|null
     */
    public function getCurrencyCode();

    /**
     * Set Currency Code
     *
     * @param string $currency_code
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionInterface
     */
    public function setCurrencyCode($currency_code);

    /**
     * Get Currency Amount
     *
     * @return float|null
     */
    public function getCurrAmount();

    /**
     * Set Currency Amount
     *
     * @param float $curr_amount
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionInterface
     */
    public function setCurrAmount($curr_amount);

    /**
     * Get Transaction Note
     *
     * @return string|null
     */
    public function getTransactionNote();

    /**
     * Set Transaction Note
     *
     * @param string $transaction_note
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionInterface
     */
    public function setTransactionNote($transaction_note);

    /**
     * Get Sender ID
     *
     * @return int|null
     */
    public function getSenderId();

    /**
     * Set Sender ID
     *
     * @param int $sender_id
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionInterface
     */
    public function setSenderId($sender_id);

    /**
     * Get Sender Type
     *
     * @return int|null
     */
    public function getSenderType();

    /**
     * Set Sender Type
     *
     * @param int $sender_type
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionInterface
     */
    public function setSenderType($sender_type);

    /**
     * Get Bank Details
     *
     * @return string|null
     */
    public function getBankDetails();

    /**
     * Set Bank Details
     *
     * @param string $bank_details
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionInterface
     */
    public function setBankDetails($bank_details);
}
