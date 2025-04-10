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
 * Webkul Walletsystem Wallet notification Interface
 */
interface WalletnotificationInterface
{
    public const  ENTITY_ID = 'entity_id';
    public const  PAYEE_COUNTER = 'payee_counter';
    public const  BANKTRANSFER_COUNTER = 'banktransfer_counter';

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
     * @return \Webkul\Walletsystem\Api\Data\WalletNotificationInterface
     */
    public function setEntityId($id);

    /**
     * Get Payee Counter
     *
     * @return int|null
     */
    public function getPayeeCounter();

    /**
     * Set Payee Counter
     *
     * @param int $payee_counter
     * @return \Webkul\Walletsystem\Api\Data\WalletNotificationInterface
     */
    public function setPayeeCounter($payee_counter);

    /**
     * Get Bank Transfer Counter
     *
     * @return int|null
     */
    public function getBankTransferCounter();

    /**
     * Set Bank Transfer Counter
     *
     * @param int $banktransfer_counter
     * @return \Webkul\Walletsystem\Api\Data\WalletNotificationInterface
     */
    public function setBankTransferCounter($banktransfer_counter);
}
