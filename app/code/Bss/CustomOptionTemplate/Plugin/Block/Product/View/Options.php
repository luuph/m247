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

namespace Bss\CustomOptionTemplate\Plugin\Block\Product\View;

use \Magento\Catalog\Model\Product\Option;

class Options
{
    /**
     * @var \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleStoreView
     */
    protected $optionVisibleStoreView;

    /**
     * @var \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleGroupCustomer
     */
    protected $optionVisibleGroupCustomer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\$customerSessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var array
     */
    protected $optionsHidden = [];

    /**
     * Options constructor.
     *
     * @param \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleStoreView $optionVisibleStoreView
     * @param \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleGroupCustomer $optionVisibleGroupCustomer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     */
    public function __construct(
        \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleStoreView $optionVisibleStoreView,
        \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleGroupCustomer $optionVisibleGroupCustomer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory
    ) {
        $this->optionVisibleStoreView = $optionVisibleStoreView;
        $this->optionVisibleGroupCustomer = $optionVisibleGroupCustomer;
        $this->storeManager = $storeManager;
        $this->customerSessionFactory = $customerSessionFactory;
    }

    /**
     * Set not require options hidden.
     *
     * @param \Magento\Catalog\Block\Product\View\Options $subject
     * @param Option $option
     * @return Option[]|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetOptionHtml(
        \Magento\Catalog\Block\Product\View\Options $subject,
        \Magento\Catalog\Model\Product\Option $option
    ) {
        $optionId = $option->getId();
        $storeId = $this->storeManager->getStore()->getId();
        $currentCustomerGroup = $this->customerSessionFactory->create()->getCustomerGroupId();
        $visibleForCustomer = $this->optionVisibleGroupCustomer->getVisibleOptionForGroupCustomer($optionId);
        $visibleForStore = $this->optionVisibleStoreView->getVisibleOptionForStoreView($optionId);
        $checkCustomer = $checkStore = true;
        if ($visibleForCustomer || $visibleForCustomer === '0') {
            $visibleForCustomer = explode(",", $visibleForCustomer);
            if (!in_array($currentCustomerGroup, $visibleForCustomer)) {
                $checkCustomer = false;
            }
        }
        if ($visibleForStore) {
            $visibleForStore = explode(",", $visibleForStore);
            if (!in_array($storeId, $visibleForStore)) {
                $checkStore = false;
            }
        }
        if ($this->checkHiddenOption($checkCustomer, $checkStore, $visibleForCustomer, $visibleForStore)) {
            $this->optionsHidden[$optionId] = true;
            $option->setIsRequire(false);
            return [$option];
        }

        return null;
    }

    /**
     * Check Visible option for customer group and store view
     *
     * @param \Magento\Catalog\Block\Product\View\Options $subject
     * @param string $result
     * @param Option $option
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetOptionHtml(
        \Magento\Catalog\Block\Product\View\Options $subject,
                                                    $result,
        \Magento\Catalog\Model\Product\Option $option
    ) {
        if (isset($this->optionsHidden[$option->getId()])) {
            return preg_replace('/class="/', 'hidden class="', $result);
        }

        return $result;
    }

    /**
     * @param bool $checkCustomer
     * @param bool $checkStore
     * @param mixed $visibleForCustomer
     * @param mixed $visibleForStore
     * @return bool
     */
    protected function checkHiddenOption($checkCustomer, $checkStore, $visibleForCustomer, $visibleForStore)
    {
        return !$checkCustomer || !$checkStore || $visibleForCustomer === '' || $visibleForStore === '';
    }

    /**
     * Sort option ASC
     *
     * @param \Magento\Catalog\Block\Product\View\Options $subject
     * @param array $options
     * @return array
     */
    public function afterGetOptions($subject, $options)
    {
        $dataOptionId = [];
        foreach ($options as $option) {
            $dataOptionId[] = $option->getOptionId();
        }
        asort($dataOptionId);
        $sortedIndexes = array_keys($dataOptionId);
        $newOptions = [];
        foreach ($sortedIndexes as $index) {
            $newOptions[] = $options[$index];
        }
        return $newOptions;
    }

}
