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
namespace Bss\RewardPoint\Cron;

use Bss\RewardPoint\Model\Config\Source\TransactionActions;
use Magento\Customer\Model\Config\Share;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class birthday
 *
 * Bss\RewardPoint\Cron
 */
class Birthday
{
    /**
     * @var \Bss\RewardPoint\Helper\RewardCustomAction
     */
    protected $helperCustomAction;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Share
     */
    protected $shareConfig;

    /**
     * Birthday constructor.
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Config\Share $shareConfig
     * @param \Bss\RewardPoint\Helper\RewardCustomAction $helperCustomAction
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Config\Share $shareConfig,
        \Bss\RewardPoint\Helper\RewardCustomAction $helperCustomAction
    ) {
        $this->storeManager       = $storeManager;
        $this->customerFactory    = $customerFactory;
        $this->shareConfig        = $shareConfig;
        $this->helperCustomAction = $helperCustomAction;
    }

    /**
     * Execute
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $customerCollection = $this->customerFactory->create();
        foreach ($customerCollection as $customer) {
            if (date('m-d') == substr($customer->getDob(), 5, 5)) {
                $options = [
                    'customerId' => $customer->getId(),
                    'storeId'    => $customer->getStoreId()
                ];
                $this->helperCustomAction->processCustomRule(TransactionActions::BIRTHDAY, $options);
            }
        }
        return $this;
    }
}
