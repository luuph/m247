<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulwalletGraphQl
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MobikulwalletGraphQl\Api;

interface ResponseInterface
{
    const RESPONSE_DATA  = 'response_data';
    const SUCCESS = 'success';
    const MESSAGE = 'message';

    /**
     * Get Success
     *
     * @return boolean|null
     */
    public function getSuccess();

    /**
     * Set Success
     * @param boolean
     * @return boolean $success
     */
    public function setsuccess($success);

    /**
     * Get response Data
     *
     * @return string|null
     */
    public function getResponseData();

    /**
     * Set response Data
     * @param string
     * @return string $responseData
     */
    public function setResponseData($responseData);

    /**
     * Get message
     *
     * @return string|null
     */
    public function getMessage();

    /**
     * Set Message
     * @param string
     * @return string $messgae
     */
    public function setMessage($messgae);
}
