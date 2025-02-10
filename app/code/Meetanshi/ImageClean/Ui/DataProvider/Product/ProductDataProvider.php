<?php

namespace Meetanshi\ImageClean\Ui\DataProvider\Product;

use Meetanshi\ImageClean\Model\ResourceModel\Images\CollectionFactory;

class ProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    protected $collection;

    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create()
            ->addAttributeToFilter('isproduct', ['eq' => 0]);
    }
}
