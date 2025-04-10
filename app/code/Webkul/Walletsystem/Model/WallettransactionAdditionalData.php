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

use Webkul\Walletsystem\Api\Data\WallettransactionAdditionalDataInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Webkul Walletsystem Class
 */
class WallettransactionAdditionalData extends AbstractModel implements WallettransactionAdditionalDataInterface
{
    public const  CACHE_TAG = 'walletsystem_wallettransactionAdditionalData';

    /**
     * @var string
     */
    protected $_cacheTag = 'walletsystem_wallettransactionAdditionalData';
    
    /**
     * @var string
     */
    protected $_eventPrefix = 'walletsystem_wallettransactionAdditionalData';
    
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Walletsystem\Model\ResourceModel\WallettransactionAdditionalData::class);
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
     * Get Transaction ID
     *
     * @return int|null
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * Set Transaction ID
     *
     * @param int $transaction_id
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setTransactionId($transaction_id)
    {
        return $this->setData(self::TRANSACTION_ID, $transaction_id);
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
