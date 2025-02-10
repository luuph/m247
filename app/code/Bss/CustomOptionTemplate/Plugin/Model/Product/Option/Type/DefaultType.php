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
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Plugin\Model\Product\Option\Type;

use Magento\Catalog\Model\Product\Option\Type\DefaultType as ExtendDefaultType;

class DefaultType
{
    /**
     * @var \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleGroupCustomer
     */
    protected $optionVisibleGroupCustomer;

    /**
     * @var \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleStoreView
     */
    protected $optionVisibleStoreView;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * DefaultType constructor.
     * @param \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleGroupCustomer $optionVisibleGroupCustomer
     * @param \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleStoreView $optionVisibleStoreView
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleGroupCustomer $optionVisibleGroupCustomer,
        \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleStoreView $optionVisibleStoreView,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Framework\App\State $state
    ) {
        $this->optionVisibleGroupCustomer = $optionVisibleGroupCustomer;
        $this->optionVisibleStoreView = $optionVisibleStoreView;
        $this->storeManager = $storeManager;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->state = $state;
    }

    /**
     * Rewrite IsRequire of option for customer group and store
     *
     * @param ExtendDefaultType $subject
     * @param mixed $values
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeValidateUserValue(ExtendDefaultType $subject, $values)
    {
        $option = $subject->getOption();

        if (!$option->getIsRequire()) {
            return [$values];
        }
        $optionId = $option->getId();
        $storeId = $this->storeManager->getStore()->getId();
        $currentCustomerGroup = $this->customerSessionFactory->create()->getCustomerGroupId();
        $visibleForCustomer = $this->optionVisibleGroupCustomer->getVisibleOptionForGroupCustomer($optionId);
        $visibleForStore = $this->optionVisibleStoreView->getVisibleOptionForStoreView($optionId);
        $checkNotCustomer = $checkNotStore = false;
        if ($visibleForCustomer || $visibleForCustomer === '0') {
            $visibleForCustomer = explode(",", $visibleForCustomer);
            if (!in_array($currentCustomerGroup, $visibleForCustomer)) {
                $checkNotCustomer = true;
            }
        }
        if ($visibleForStore) {
            $visibleForStore = explode(",", $visibleForStore);
            if (!in_array($storeId, $visibleForStore)) {
                $checkNotStore = true;
            }
        }
        if ($this->checkHiddenOption($checkNotCustomer, $checkNotStore, $visibleForCustomer, $visibleForStore)) {
            $option->setIsRequire(false);
            return [$values];
        }

        return [$values];
    }

    /**
     * @param bool $checkNotCustomer
     * @param bool $checkNotStore
     * @param mixed $visibleForCustomer
     * @param mixed $visibleForStore
     * @return bool
     */
    protected function checkHiddenOption($checkNotCustomer, $checkNotStore, $visibleForCustomer, $visibleForStore)
    {
        return $checkNotCustomer || $checkNotStore || $visibleForCustomer === '' || $visibleForStore === '';
    }
}
