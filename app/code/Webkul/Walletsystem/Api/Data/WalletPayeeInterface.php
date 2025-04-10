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
 * Webkul Walletsystem Wallet Payee Interface
 */
interface WalletPayeeInterface
{
    public const  ENTITY_ID = 'entity_id';

    public const CUSTOMER_ID = 'customer_id';

    public const WEBSITE_ID = 'website_id';

    public const PAYEE_CUSTOMER_ID = 'payee_customer_id';

    public const NICK_NAME = 'nick_name';

    public const  STATUS = 'status';

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
     * @return \Webkul\Walletsystem\Api\Data\WalletPayeeInterface
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
     * @return \Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setCustomerId($customer_id);

    /**
     * Get Website Id
     *
     * @return int|null
     */
    public function getWebsiteId();
    
    /**
     * Set Website Id
     *
     * @param int $website_id
     */
    public function setWebsiteId($website_id);

    /**
     * Get Payee Customer ID
     *
     * @return int|null
     */
    public function getPayeeCustomerId();

    /**
     * Set Payee Customer ID
     *
     * @param int $payee_customer_id
     * @return \Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setPayeeCustomerId($payee_customer_id);

    /**
     * Get Nick Name
     *
     * @return string|null
     */
    public function getNickName();

    /**
     * Set Nick Name
     *
     * @param string $nick_name
     * @return \Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setNickName($nick_name);

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
     * @return \Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setStatus($status);
}
