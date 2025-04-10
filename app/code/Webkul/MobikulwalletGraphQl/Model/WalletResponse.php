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
namespace Webkul\MobikulwalletGraphQl\Model;

use Webkul\MobikulwalletGraphQl\Api\ResponseInterface;

class WalletResponse extends \Magento\Framework\DataObject implements ResponseInterface
{
    /**
     * Get Success
     *
     * @return boolean|null
     */
    public function getSuccess()
    {
        return $this->getData(self::SUCCESS);
    }

    /**
     * Set Success
     * @param boolean
     * @return boolean $success
     */
    public function setSuccess($success)
    {
        return $this->setData(self::SUCCESS, $success);
    }

    /**
     * Get Message
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * Set Message
     * @param string
     * @return string $messgae
     */
    public function setMessage($messgae)
    {
        return $this->setData(self::MESSAGE, $messgae);
    }

    /**
     * Get Response Data
     *
     * @return string|null
     */
    public function getResponseData()
    {
        return $this->getData(self::RESPONSE_DATA);
    }

    /**
     * Set Response Data
     * @param string
     * @return string $data
     */
    public function setResponseData($data)
    {
        return $this->setData(self::RESPONSE_DATA, $data);
    }
}
