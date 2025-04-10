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

namespace Webkul\Walletsystem\Controller\Adminhtml\Transfer;

use Webkul\Walletsystem\Controller\Adminhtml\Transfer as TransferController;
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul Walletsystem Class
 */
class Payeelist extends TransferController
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\Walletsystem\Model\WalletNotification
     */
    protected $notification;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\Walletsystem\Model\WalletNotification $notification
     * @param \Webkul\Walletsystem\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\Walletsystem\Model\WalletNotification $notification,
        \Webkul\Walletsystem\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->notification = $notification;
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Walletsystem::walletpayee');
        $resultPage->getConfig()->getTitle()->prepend(__('Wallet System Payee Details'));
        $resultPage->addBreadcrumb(__('Wallet System Payee Details'), __('Wallet System Payee Details'));
        $notifications = $this->notification->getCollection();
        foreach ($notifications->getItems() as $notification) {
            $notification->setPayeeCounter(0);
            $this->helper->saveObject($notification);
        }
        $this->helper->saveObject($notifications);
        return $resultPage;
    }
}
