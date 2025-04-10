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

use Webkul\Walletsystem\Api\Data\WalletPayeeInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Webkul Walletsystem Class
 */
class WalletPayee extends AbstractModel implements WalletPayeeInterface, IdentityInterface
{
    public const  CACHE_TAG = 'walletsystem_walletpayee';

    public const  PAYEE_STATUS_ENABLE = 1;
    public const  PAYEE_STATUS_DISABLE = 0;
    
    /**
     * @var string
     */
    protected $_cacheTag = 'walletsystem_walletpayee';
    
    /**
     * @var string
     */
    protected $_eventPrefix = 'walletsystem_walletpayee';
    
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Walletsystem\Model\ResourceModel\WalletPayee::class);
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
     * @return \Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setCustomerId($customer_id)
    {
        return $this->setData(self::CUSTOMER_ID, $customer_id);
    }

    /**
     * Get Website Id
     *
     * @return int|null
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }
    
    /**
     * Set Website Id
     *
     * @param int $website_id
     * @return int|null
     */
    public function setWebsiteId($website_id)
    {
        return $this->setData(self::WEBSITE_ID, $website_id);
    }

    /**
     * Get Payee Customer ID
     *
     * @return int|null
     */
    public function getPayeeCustomerId()
    {
        return $this->getData(self::PAYEE_CUSTOMER_ID);
    }

    /**
     * Set Payee Customer ID
     *
     * @param int $payee_customer_id
     * @return \Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setPayeeCustomerId($payee_customer_id)
    {
        return $this->setData(self::PAYEE_CUSTOMER_ID, $payee_customer_id);
    }

    /**
     * Get Nick Name
     *
     * @return string|null
     */
    public function getNickName()
    {
        return $this->getData(self::NICK_NAME);
    }

    /**
     * Set Nick Name
     *
     * @param string $nick_name
     * @return \Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setNickName($nick_name)
    {
        return $this->setData(self::NICK_NAME, $nick_name);
    }

    /**
     * Set Status
     *
     * @param int $status
     * @return Webkul\Walletsystem\Api\Data\WalletPayeeInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
}
