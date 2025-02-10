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

class CustomerSubscribed implements ObserverInterface
{
    /**
     * @var \Bss\RewardPoint\Helper\RewardCustomAction
     */
    protected $helperCustomAction;
    /**
     * CustomerSubscribed constructor.
     * @param \Bss\RewardPoint\Helper\RewardCustomAction $helperCustomAction
     */
    public function __construct(
        \Bss\RewardPoint\Helper\RewardCustomAction $helperCustomAction
    ) {
        $this->helperCustomAction = $helperCustomAction;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $subscriber \Magento\Newsletter\Model\Subscriber */
        $subscriber = $observer->getEvent()->getSubscriber();
        if (!$subscriber->isObjectNew() || !$subscriber->getCustomerId()) {
            return $this;
        }

        $options = [
                    'customerId' => $subscriber->getCustomerId(),
                    'storeId'    => $subscriber->getStoreId()
                   ];
        $this->helperCustomAction->processCustomRule(TransactionActions::SUBSCRIBLE_NEWSLETTERS, $options);
    }
}
