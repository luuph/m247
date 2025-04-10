<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Model;

use Webkul\Walletsystem\Api\Data\WallettransactionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Webkul Walletsystem Class
 */
class Wallettransaction extends AbstractModel implements WallettransactionInterface, IdentityInterface
{
    public const  CACHE_TAG = 'walletsystem_wallettransaction';
    public const  ORDER_PLACE_TYPE = 0;
    public const  CASH_BACK_TYPE = 1;
    public const  REFUND_TYPE = 2;
    public const  ADMIN_TRANSFER_TYPE = 3;
    public const  CUSTOMER_TRANSFER_TYPE = 4;
    public const  CUSTOMER_TRANSFER_BANK_TYPE = 5;

    public const  WALLET_ACTION_TYPE_DEBIT = 'debit';
    public const  WALLET_ACTION_TYPE_CREDIT = 'credit';

    public const  WALLET_TRANS_STATE_PENDING = 0;
    public const  WALLET_TRANS_STATE_APPROVE = 1;
    public const  WALLET_TRANS_STATE_CANCEL = 2;

    /**
     * @var string
     */
    protected $_cacheTag = 'walletsystem_wallettransaction';
    
    /**
     * @var string
     */
    protected $_eventPrefix = 'walletsystem_wallettransaction';
    
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Walletsystem\Model\ResourceModel\Wallettransaction::class);
    }
    
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set Entity ID
     *
     * @param int $id
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Customer ID
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set Customer ID
     *
     * @param int $customer_id
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setCustomerId($customer_id)
    {
        return $this->setData(self::CUSTOMER_ID, $customer_id);
    }

    /**
     * Get Amount
     *
     * @return float|null
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * Set Amount
     *
     * @param float $amount
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Entity ID
     *
     * @param int $status
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Action
     *
     * @return string|null
     */
    public function getAction()
    {
        return $this->getData(self::ACTION);
    }

    /**
     * Set Action
     *
     * @param string $action
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setAction($action)
    {
        return $this->setData(self::ACTION, $action);
    }

    /**
     * Get Order ID
     *
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set Order ID
     *
     * @param int $order_id
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setOrderId($order_id)
    {
        return $this->setData(self::ORDER_ID, $order_id);
    }

    /**
     * Get Transaction Time
     *
     * @return date|null
     */
    public function getTransactionAt()
    {
        return $this->getData(self::TRANSACTION_AT);
    }

    /**
     * Set Transaction Time
     *
     * @param date $transaction_at
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setTransactionAt($transaction_at)
    {
        return $this->setData(self::TRANSACTION_AT, $transaction_at);
    }

    /**
     * Get Currency Code
     *
     * @return string|null
     */
    public function getCurrencyCode()
    {
        return $this->getData(self::CURRENCY_CODE);
    }

    /**
     * Set Currency Code
     *
     * @param string $currency_code
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setCurrencyCode($currency_code)
    {
        return $this->setData(self::CURRENCY_CODE, $currency_code);
    }

    /**
     * Get Currency Amount
     *
     * @return float|null
     */
    public function getCurrAmount()
    {
        return $this->getData(self::CURR_AMOUNT);
    }

    /**
     * Set Currency Amount
     *
     * @param float $curr_amount
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setCurrAmount($curr_amount)
    {
        return $this->setData(self::CURR_AMOUNT, $curr_amount);
    }

    /**
     * Get Transaction Note
     *
     * @return string|null
     */
    public function getTransactionNote()
    {
        return $this->getData(self::TRANSACTION_NOTE);
    }

    /**
     * Set Transaction Note
     *
     * @param string $transaction_note
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setTransactionNote($transaction_note)
    {
        return $this->setData(self::TRANSACTION_NOTE, $transaction_note);
    }

    /**
     * Get Sender ID
     *
     * @return int|null
     */
    public function getSenderId()
    {
        return $this->getData(self::SENDER_ID);
    }

    /**
     * Set Sender ID
     *
     * @param int $sender_id
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setSenderId($sender_id)
    {
        return $this->setData(self::SENDER_ID, $sender_id);
    }

    /**
     * Get Sender Type
     *
     * @return int|null
     */
    public function getSenderType()
    {
        return $this->getData(self::SENDER_TYPE);
    }

    /**
     * Set Sender Type
     *
     * @param int $sender_type
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setSenderType($sender_type)
    {
        return $this->setData(self::SENDER_TYPE, $sender_type);
    }

    /**
     * Get Bank Details
     *
     * @return string|null
     */
    public function getBankDetails()
    {
        return $this->getData(self::BANK_DETAILS);
    }

    /**
     * Set Bank Details
     *
     * @param string $bank_details
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setBankDetails($bank_details)
    {
        return $this->setData(self::BANK_DETAILS, $bank_details);
    }
}
