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

use Webkul\Walletsystem\Api\Data\WalletnotificationInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Webkul Walletsystem Class
 */
class WalletNotification extends AbstractModel implements walletnotificationInterface, IdentityInterface
{

    /**
     * No route page id
     */
    public const  NOROUTE_ENTITY_ID = 'no-route';

    /**
     * WalletNotification WalletNotification cache tag
     */
    public const  CACHE_TAG = 'walletsystem_walletnotification';

    /**
     * @var string
     */
    protected $_cacheTag = 'walletsystem_walletnotification';

    /**
     * @var string
     */
    protected $_eventPrefix = 'walletsystem_walletnotification';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Walletsystem\Model\ResourceModel\WalletNotification::class);
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteWalletNotification();
        }
        return parent::load($id, $field);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\WalletNotification\Api\Data\WalletNotificationInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Payee Counter
     *
     * @return int
     */
    public function getPayeeCounter()
    {
        return parent::getData(self::PAYEE_COUNTER);
    }

    /**
     * Set Payee Counter
     *
     * @param int $payee_counter
     * @return \Webkul\WalletNotification\Api\Data\WalletNotificationInterface
     */
    public function setPayeeCounter($payee_counter)
    {
        return $this->setData(self::PAYEE_COUNTER, $payee_counter);
    }

    /**
     * Get Bank Transfer Counter
     *
     * @return int
     */
    public function getBankTransferCounter()
    {
        return parent::getData(self::BANKTRANSFER_COUNTER);
    }

    /**
     * Set Bank Transfer Counter
     *
     * @param int $banktransfer_counter
     * @return \Webkul\WalletNotification\Api\Data\WalletNotificationInterface
     */
    public function setBankTransferCounter($banktransfer_counter)
    {
        return $this->setData(self::BANKTRANSFER_COUNTER, $banktransfer_counter);
    }
}
