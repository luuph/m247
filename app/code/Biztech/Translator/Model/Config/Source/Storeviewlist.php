<?php
namespace Biztech\Translator\Model\Config\Source;

use Biztech\Translator\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

class Storeviewlist implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    protected $helper;

   

 
    public function __construct(
        StoreManagerInterface $storeManager,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $websites = $this->helper->getAllWebsites();
        foreach ($this->_storeManager->getWebsites() as $website) {
            $storeView = [];
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    if ($store && in_array($store->getId(), $websites)) {
                        $id = $store->getId();
                        $name = $store->getName();
                        $storeView[] = ['value' => $id, 'label' => __($name)];
                    }
                }
            }
            if (!empty($storeView)) {
                    $this->_options[] = [
                    'label' => __($website->getName()),
                    'value' => $storeView
                    ];
            }
        }
        if (empty($this->_options)) {
            $this->_options[] = [
                'label' => __("Activated storeview not found"),
                'value' => '',
                'style' => "color: #fff;background-color: #dc3545;border-color: #dc3545;",
            ];
        }
        return $this->_options;
    }
}
