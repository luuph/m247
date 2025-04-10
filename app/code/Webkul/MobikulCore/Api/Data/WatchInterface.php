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
 * Interface WatchInterface
 */
interface WatchInterface
{
    public const ID = "id";
    public const QRURL = "qr_url";
    public const CUSTOMER_ID = "customer_id";
    public const CUSTOMER_TOKEN = "customer_token";

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
     * Function getQrUrl
     *
     * @return string
     */
    public function getQrUrl();

    /**
     * Function setQrUrl
     *
     * @param string $qr_url
     */
    public function setQrUrl($qr_url);

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

    /**
     * Function getCustomerToken
     *
     * @return string
     */
    public function getCustomerToken();

    /**
     * Function setCustomerToken
     *
     * @param string $customerToken customerToken
     */
    public function setCustomerToken($customerToken);
}
