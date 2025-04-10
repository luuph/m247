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

use Webkul\Walletsystem\Api\Data\AccountDetailsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

/**
 * Webkul Walletsystem Class
 */
class AccountDetails extends AbstractModel implements AccountDetailsInterface, IdentityInterface
{
    /**
     * @var CACHE_TAG
     */
    public const  CACHE_TAG = 'wk_ws_wallet_account_details';
    
    /**
     * @var EventPrefix
     */
    protected $_eventPrefix = 'wk_ws_wallet_account_details';
    
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Walletsystem\Model\ResourceModel\AccountDetails::class);
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
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
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
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setCustomerId($customer_id)
    {
        return $this->setData(self::CUSTOMER_ID, $customer_id);
    }
    
    /**
     * Get Request To Delete
     *
     * @return int|null
     */
    public function getRequestToDelete()
    {
        return $this->getData(self::REQUEST_TO_DELETE);
    }

    /**
     * Set Request To Delete
     *
     * @param int $request_to_delete
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setRequestToDelete($request_to_delete)
    {
        return $this->setData(self::REQUEST_TO_DELETE, $request_to_delete);
    }

    /**
     * Get Holder Name
     *
     * @return string|null
     */
    public function getHolderName()
    {
        return $this->getData(self::HOLDERNAME);
    }

    /**
     * Set Request To Delete
     *
     * @param string $holdername
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setHolderName($holdername)
    {
        return $this->setData(self::HOLDERNAME, $holdername);
    }

    /**
     * Get Account Number
     *
     * @return int|null
     */
    public function getAccountNo()
    {
        return $this->getData(self::ACCOUNTNO);
    }

    /**
     * Set Account Number
     *
     * @param int $accountno
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setAccountNo($accountno)
    {
        return $this->setData(self::ACCOUNTNO, $accountno);
    }

    /**
     * Get Bank Name
     *
     * @return string|null
     */
    public function getBankName()
    {
        return $this->getData(self::BANKNAME);
    }

    /**
     * Set Bank Code
     *
     * @param string $bankname
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setBankName($bankname)
    {
        return $this->setData(self::BANKNAME, $bankname);
    }

    /**
     * Get Bank Code
     *
     * @return string|null
     */
    public function getBankCode()
    {
        return $this->getData(self::BANKCODE);
    }

    /**
     * Set Bank Code
     *
     * @param string $bankcode
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setBankCode($bankcode)
    {
        return $this->setData(self::BANKCODE, $bankcode);
    }

    /**
     * Get Additional
     *
     * @return string|null
     */
    public function getAdditional()
    {
        return $this->getData(self::ADDITIONAL);
    }

    /**
     * Set Additional
     *
     * @param string $additional
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setAdditional($additional)
    {
        return $this->setData(self::ADDITIONAL, $additional);
    }
}
