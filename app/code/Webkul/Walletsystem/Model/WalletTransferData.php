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

use \Magento\Framework\Model\AbstractModel;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Webkul Walletsystem Class
 */
class WalletTransferData extends AbstractModel
{

    /**
     * @var CustomerSession
     */
    protected $customerSession;
    
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $walletHelper;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Initialize dependencies
     *
     * @param CustomerSession                        $customerSession
     * @param MagentoFrameworkStdlibDateTimeDateTime $date
     * @param WebkulWalletsystemHelperData           $walletHelper
     */
    public function __construct(
        CustomerSession $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Walletsystem\Helper\Data $walletHelper
    ) {
        $this->customerSession = $customerSession;
        $this->date = $date;
        $this->walletHelper = $walletHelper;
    }

    /**
     * Get wallet code from session
     *
     * @return value
     */
    public function getWalletTransferDataToSession()
    {
        return $this->customerSession->getWalletTransferData();
    }
    
    /**
     * Set Wallet Transfer Data To Session
     *
     * @param array $value
     */
    public function setWalletTransferDataToSession($value)
    {
        $this->customerSession->setWalletTransferData($value);
    }
    /**
     * Delete  wallet transfer data from session
     */
    public function deleteWalletTransferDataToSession()
    {
        $this->customerSession->setWalletTransferData('');
    }

    /**
     * Check and update session
     */
    public function checkAndUpdateSession()
    {
        $validationDuration = $this->walletHelper->getCodeValidationDuration();
        $sessionData = $this->walletHelper->convertStringAccToVersion(
            $this->getWalletTransferDataToSession(),
            'decode'
        );
        if (is_array($sessionData) &&
            array_key_exists('created_at', $sessionData) &&
            !empty($sessionData['created_at'])) {
            $currentTime = $this->date->gmtDate();
            $difference =  strtotime($currentTime) - $sessionData['created_at'];
            if ($difference > $validationDuration) {
                $this->deleteWalletTransferDataToSession();
            }
        }
    }
}
