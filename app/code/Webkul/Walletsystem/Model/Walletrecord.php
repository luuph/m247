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

use Webkul\Walletsystem\Api\Data\WalletrecordInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

/**
 * Webkul Walletsystem Class
 */
class Walletrecord extends AbstractModel implements WalletrecordInterface, IdentityInterface
{
    public const  CACHE_TAG = 'walletsystem_walletrecord';
    
    /**
     * @var string
     */
    protected $_cacheTag = 'walletsystem_walletrecord';
    
    /**
     * @var string
     */
    protected $_eventPrefix = 'walletsystem_walletrecord';
    
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Walletsystem\Model\ResourceModel\Walletrecord::class);
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
     * @return \Webkul\Walletsystem\Api\Data\WalletrecordInterface
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Customer ID
     *
     * @return float|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set Customer ID
     *
     * @param float $total_amount
     * @return \Webkul\Walletsystem\Api\Data\WalletrecordInterface
     */
    public function setCustomerId($total_amount)
    {
        return $this->setData(self::CUSTOMER_ID, $total_amount);
    }

    /**
     * Get Total Amount
     *
     * @return float|null
     */
    public function getTotalAmount()
    {
        return $this->getData(self::TOTAL_AMOUNT);
    }

    /**
     * Set Total Amount
     *
     * @param float $total_amount
     * @return \Webkul\Walletsystem\Api\Data\WalletrecordInterface
     */
    public function setTotalAmount($total_amount)
    {
        return $this->setData(self::TOTAL_AMOUNT, $total_amount);
    }

    /**
     * Get Remaining Amount
     *
     * @return float|null
     */
    public function getRemainingAmount()
    {
        return $this->getData(self::REMAINING_AMOUNT);
    }

    /**
     * Set Remaining Amount
     *
     * @param float $remaining_amount
     * @return \Webkul\Walletsystem\Api\Data\WalletrecordInterface
     */
    public function setRemainingAmount($remaining_amount)
    {
        return $this->setData(self::REMAINING_AMOUNT, $remaining_amount);
    }

    /**
     * Get Used Amount
     *
     * @return float|null
     */
    public function getUsedAmount()
    {
        return $this->getData(self::USED_AMOUNT);
    }

    /**
     * Set Remaining Amount
     *
     * @param float $used_amount
     * @return \Webkul\Walletsystem\Api\Data\WalletrecordInterface
     */
    public function setUsedAmount($used_amount)
    {
        return $this->setData(self::USED_AMOUNT, $used_amount);
    }

    /**
     * Get Updated Time
     *
     * @return date|null
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * Set Updated Time
     *
     * @param date $updated_at
     * @return \Webkul\Walletsystem\Api\Data\WalletrecordInterface
     */
    public function setUpdatedAt($updated_at)
    {
        return $this->setData(self::UPDATED_AT, $updated_at);
    }
}
