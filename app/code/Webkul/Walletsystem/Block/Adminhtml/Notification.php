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

namespace Webkul\Walletsystem\Block\Adminhtml;

/**
 * Webkul Walletsystem Notification Block
 */
class Notification extends \Magento\Backend\Block\Template
{
    /**
     * @var \Webkul\Walletsystem\Model\WalletNotification
     */
    public $walletNotification;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    public $assetRepo;
    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Webkul\Walletsystem\Model\WalletNotification $walletNotification
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Webkul\Walletsystem\Model\WalletNotification $walletNotification,
        array $data = []
    ) {
        $this->walletNotification = $walletNotification;
        $this->assetRepo = $assetRepo;
        parent::__construct($context, $data);
    }

    /**
     * Get payee notification count
     *
     * @return int
     */
    public function getPayeeNotificationCount()
    {
        $notifications = $this->walletNotification->getCollection();
        if (!$notifications->getSize()) {
            return false;
        } else {
            foreach ($notifications->getItems() as $notification) {
                $data = $notification->getPayeeCounter();
            }
            return $data;
        }
    }

    /**
     * Get bank transfer counter
     *
     * @return bool|array
     */
    public function getBankTransferCounter()
    {
        $notifications = $this->walletNotification->getCollection();
        if (!$notifications->getSize()) {
            return false;
        } else {
            foreach ($notifications->getItems() as $notification) {
                $data = $notification->getBanktransferCounter();
            }
            return $data;
        }
    }

    /**
     * Get image url
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->assetRepo->getUrl('Webkul_Walletsystem::images/icons_notifications.png');
    }
}
