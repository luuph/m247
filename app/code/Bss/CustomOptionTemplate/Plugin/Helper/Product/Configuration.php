<?php
namespace Bss\CustomOptionTemplate\Plugin\Helper\Product;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

class Configuration
{
    
    protected $storeManager;
    protected $optionVisibleGroupCustomer;
    protected $optionVisibleStoreView;
    protected $customerSessionFactory;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleGroupCustomer $optionVisibleGroupCustomer,
        \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleStoreView $optionVisibleStoreView,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory
    ) {
        $this->optionVisibleGroupCustomer = $optionVisibleGroupCustomer;
        $this->optionVisibleStoreView = $optionVisibleStoreView;
        $this->storeManager = $storeManager;
        $this->customerSessionFactory = $customerSessionFactory;
    }

    public function afterGetCustomOptions($subject, $result, ItemInterface $item)
    {
        if(!empty($result)) {
            foreach($result as $key => $option) {
                $optionId = $option['option_id'];
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
                if (!$checkCustomer || !$checkStore) {
                    unset($result[$key]);
                }
            }
            return $result;
        }
        return $result;
    }
}