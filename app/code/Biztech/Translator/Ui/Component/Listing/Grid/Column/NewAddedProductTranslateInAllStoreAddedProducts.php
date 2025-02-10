<?php
namespace Biztech\Translator\Ui\Component\Listing\Grid\Column;

class NewAddedProductTranslateInAllStoreAddedProducts extends \Magento\Ui\Component\Listing\Columns\Column
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
                $total_products = json_decode($item['product_ids']);
                $product_count = count($total_products);
                $item['product_ids'] = $product_count;
            }
        }
        return $dataSource;
    }
}
