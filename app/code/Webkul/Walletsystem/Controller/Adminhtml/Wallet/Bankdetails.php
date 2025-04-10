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

namespace Webkul\Walletsystem\Controller\Adminhtml\Wallet;

use Webkul\Walletsystem\Controller\Adminhtml\Wallet as WalletController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;

/**
 * Webkul Walletsystem Class
 */
class Bankdetails extends WalletController
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;

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
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     * @param \Webkul\Walletsystem\Model\WalletNotification $notification
     * @param \Webkul\Walletsystem\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        \Webkul\Walletsystem\Model\WalletNotification $notification,
        \Webkul\Walletsystem\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->customerModel = $customerModel;
        $this->notification = $notification;
        $this->helper = $helper;
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Walletsystem::banktransfer');
        $resultPage->getConfig()->getTitle()
            ->prepend(__('Bank Transfer Details'));
        $resultPage->addBreadcrumb(
            __("Bank Transfer Details"),
            __("Bank Transfer Details")
        );
        $notifications = $this->notification->getCollection();
        foreach ($notifications->getItems() as $notification) {
            $notification->setBanktransferCounter(0);
            $this->helper->saveObject($notification);
        }
        $this->helper->saveObject($notifications);
        return $resultPage;
    }
}
