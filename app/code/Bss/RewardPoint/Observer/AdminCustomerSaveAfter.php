<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Bss\RewardPoint\Model\Config\Source\TransactionActions;

/**
 * Class Admin CustomerSaveAfter
 *
 * Bss\RewardPoint\Observer
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AdminCustomerSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Bss\RewardPoint\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\RewardPoint\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Bss\RewardPoint\Model\NotificationFactory
     */
    protected $notificationFactory;

    /**
     * AdminCustomerSaveAfter constructor.
     * @param \Bss\RewardPoint\Model\TransactionFactory $transactionFactory
     * @param \Bss\RewardPoint\Model\NotificationFactory $notificationFactory
     * @param \Bss\RewardPoint\Helper\Data $helper
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        \Bss\RewardPoint\Model\TransactionFactory $transactionFactory,
        \Bss\RewardPoint\Model\NotificationFactory $notificationFactory,
        \Bss\RewardPoint\Helper\Data $helper,
        \Magento\Backend\Model\Auth\Session $authSession,
        ManagerInterface $messageManager
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->notificationFactory = $notificationFactory;
        $this->helper = $helper;
        $this->authSession = $authSession;
        $this->messageManager = $messageManager;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $this->_saveTransaction($request);
        $this->_saveNotification($request);
    }

    /**
     * Save transaction
     *
     * @param \Magento\Framework\App\RequestInterface $request
     */
    protected function _saveTransaction($request)
    {
        $data = $request->getParam('rwp', []);
        $data_customer = $request->getParam('customer', []);
        $admin = $this->authSession->getUser();
        if (!empty($data)) {
            $point = (int) $data['point'];
            if ($point !== 0) {
                $data['customer_id'] = $data_customer['entity_id'];
                $data['action'] = TransactionActions::ADMIN_CHANGE;
                $data['created_by'] = $admin->getEmail();
                $data['created_at'] = $this->helper->getCreateAt();
                $data['expires_at'] = $this->helper->getExpireDay($data['website_id']);
                $data['is_expired'] = (bool)$this->helper->getExpireDay($data['website_id']);

                /** @var \Bss\RewardPoint\Model\Transaction $transaction */
                $transaction = $this->transactionFactory->create();

                try {
                    $transaction->setData($data);
                    if ($point < 0) {
                        $transaction->updatePointUsed();
                    }
                    $transaction->save();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Something went wrong while saving the reward points.')
                    );
                }
            }
        }
    }

    /**
     * Save notification
     *
     * @param \Magento\Framework\App\RequestInterface $request
     */
    protected function _saveNotification($request)
    {
        $data = $request->getParam('rwp_notify', []);
        $data_customer = $request->getParam('customer', []);
        if (!empty($data)) {
            /** @var \Bss\RewardPoint\Model\Notification $notify */
            $notify = $this->notificationFactory->create();

            $data['customer_id'] = $data_customer['entity_id'];
            $notify->load($data['customer_id']);
            try {
                $notify->setData($data);
                $notify->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the notification.')
                );
            }
        }
    }
}
