<?php

namespace Meetanshi\ImageClean\Ui\Component\Listing\Custom\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Model\Customer as Custom;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;

class ProductStatus extends Column
{
    protected $systemStore;
    protected $storeManager;
    protected $helper;
    protected $customer;
    protected $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Custom $customer,
        Escaper $escaper,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->customer = $customer;
        $this->escaper = $escaper;
        $this->storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource){
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item['used']):
                    $status = 'Used Image';
                else:
                    $status = "Unused Image";
                endif;
                $item['used'] = $status;
            }
        }
        return $dataSource;
    }
}