<?php

namespace Chill\Vortex\Plugin\Ui\Component\Listing\Columns;

use Magento\Catalog\Api\ProductRepositoryInterface;

class AddCustomColumn
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    public function afterPrepareDataSource($subject, $result)
    {
        foreach ($result['data']['items'] as &$item) {
            try {
                $product = $this->productRepository->getById($item['entity_id']);
                $item['custom_column'] = $product->getData('hideprice_action'); // Thay `custom_attribute` bằng field bạn muốn hiển thị.
            } catch (\Exception $e) {
                $item['custom_column'] = __('N/A');
            }
        }
        return $result;
    }
}
