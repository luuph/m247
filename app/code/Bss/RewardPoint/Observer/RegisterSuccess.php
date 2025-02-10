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
use Bss\RewardPoint\Model\Config\Source\TransactionActions;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Bss\RewardPoint\Helper\RewardCustomAction;
use Bss\RewardPoint\Helper\Data;
use Bss\RewardPoint\Model\NotificationFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Register Success
 *
 * Bss\RewardPoint\Observer
 */
class RegisterSuccess implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $bssHelper;

    /**
     * @var Subscriber
     */
    protected $subscriberModel;

    /**
     * @var RewardCustomAction
     */
    protected $helperCustomAction;

    /**
     * @var NotificationFactory
     */
    protected $notificationFactory;

    /**
     * RegisterSuccess constructor.
     *
     * @param Subscriber $subscriberModel
     * @param RewardCustomAction $helperCustomAction
     * @param Data $bssHelper
     * @param NotificationFactory $notificationFactory
     */
    public function __construct(
        Subscriber $subscriberModel,
        RewardCustomAction $helperCustomAction,
        Data $bssHelper,
        NotificationFactory $notificationFactory
    ) {
        $this->subscriberModel = $subscriberModel;
        $this->helperCustomAction = $helperCustomAction;
        $this->bssHelper = $bssHelper;
        $this->notificationFactory = $notificationFactory;
    }

    /**
     * Process reward point action when customer was created
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $options =  [
                        'customerId' => $customer->getId(),
                        'storeId'    => $customer->getStoreId()
                    ];
        $this->helperCustomAction->processCustomRule(TransactionActions::REGISTRATION, $options);
        $isSubcribeCustomer = $this->bssHelper->getFlagConfig(
            Data::XML_PATH_SUBSCRIBLE,
            ScopeInterface::SCOPE_STORE,
            $customer->getStoreId()
        );
        if ($isSubcribeCustomer) {
            $notifyData = [
                'customer_id' => $customer->getId(),
                'notify_balance' => 1,
                'notify_expiration' => 1
            ];
            $notify = $this->notificationFactory->create();
            $notify->setData($notifyData);
            $notify->save();
        }
        if ($this->isCustomerSubscribed($customer)) {
            $this->helperCustomAction->processCustomRule(TransactionActions::SUBSCRIBLE_NEWSLETTERS, $options);
        }
        if ($customer->getDob()) {
            if (date('m-d') == substr($customer->getDob(), 5, 5)) {
                $this->helperCustomAction->processCustomRule(TransactionActions::BIRTHDAY, $options);
            }
        }
    }

    /**
     * Check customer subcriber
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return bool
     */
    protected function isCustomerSubscribed($customer)
    {
        $subscriber = $this->subscriberModel->loadByEmail($customer->getEmail());
        if ($subscriber && $subscriber['subscriber_status']) {
            return true;
        }
        return false;
    }
}
