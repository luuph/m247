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
 * Webkul Walletsystem Wallet transaction Additional Data Interface
 */
interface WallettransactionAdditionalDataInterface
{
    public const  ENTITY_ID = 'entity_id';
    public const  TRANSACTION_ID = 'transaction_id';
    public const  ADDITIONAL = 'additional';

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
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionAdditionalDataInterface
     */
    public function setEntityId($id);

    /**
     * Get Transaction ID
     *
     * @return int|null
     */
    public function getTransactionId();

    /**
     * Set Transaction ID
     *
     * @param int $transaction_id
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionAdditionalDataInterface
     */
    public function setTransactionId($transaction_id);

    /**
     * Get Additional
     *
     * @return string|null
     */
    public function getAdditional();

    /**
     * Set Additional
     *
     * @param string $additional
     * @return \Webkul\Walletsystem\Api\Data\WallettransactionAdditionalDataInterface
     */
    public function setAdditional($additional);
}
