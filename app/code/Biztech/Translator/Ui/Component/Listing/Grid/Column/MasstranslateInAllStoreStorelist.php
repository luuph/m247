<?php
namespace Biztech\Translator\Ui\Component\Listing\Grid\Column;

class MasstranslateInAllStoreStorelist extends \Magento\Ui\Component\Listing\Columns\Column
{

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Biztech\Translator\Helper\Data $datahelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $datahelper;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $stores = json_decode($item['store_ids']);
                $store_view = '';
                foreach ($stores as $key => $value) {
                    $storeName = ucfirst($this->_storeManager->getStore($value)->getName());
                    $store_view.= $storeName."<br>";
                }
                $item['store_ids'] = $store_view;
            }
        }
        return $dataSource;
    }
}
