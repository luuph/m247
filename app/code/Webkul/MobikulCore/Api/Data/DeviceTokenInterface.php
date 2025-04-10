<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Api\Data;

/**
 * Interface DeviceTokenInterface
 */
interface DeviceTokenInterface
{
    public const ID = "id";
    public const TOKEN = "token";
    public const CUSTOMER_ID = "customer_id";

    /**
     * Function getId
     *
     * @return integer
     */
    public function getId();

    /**
     * Function setId
     *
     * @param integer $id id
     */
    public function setId($id);

    /**
     * Function getToken
     *
     * @return string
     */
    public function getToken();

    /**
     * Function setToken
     *
     * @param string $token token
     */
    public function setToken($token);

    /**
     * Function getCustomerId
     *
     * @return integer
     */
    public function getCustomerId();

    /**
     * Function setCustomerId
     *
     * @param integer $customerId customerId
     */
    public function setCustomerId($customerId);
}
