<?php
namespace Biztech\Translator\Ui\Component\Listing\Grid\Column;

class NewAddedProductTranslateInAllStoreSuccessStore extends \Magento\Ui\Component\Listing\Columns\Column
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
                $success_stores = explode(",", !$item['succeed_store_ids']==null ?$item['succeed_store_ids']:'');
                $success_store = '';
                foreach ($success_stores as $successStore) {
                    if ($successStore!=null && $successStore!="" && $successStore!=" ") {
                        $storeName = ucfirst($this->_storeManager->getStore($successStore)->getName());
                        $success_store.= $storeName."<br>";
                    }
                }
                $item['succeed_store_ids'] = $success_store;
            }
        }
        return $dataSource;
    }
}
