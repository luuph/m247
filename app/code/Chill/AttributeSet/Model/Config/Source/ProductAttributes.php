<?php
namespace Chill\AttributeSet\Model\Config\Source;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class ProductAttributes implements OptionSourceInterface
{
    protected $attributeRepository;
    protected $searchCriteriaBuilder;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function toOptionArray()
    {
        $options = [];

        // Tạo search criteria (rỗng = lấy tất cả)
        $searchCriteria = $this->searchCriteriaBuilder->create();

        // Lấy danh sách attribute của catalog_product
        $attributes = $this->attributeRepository->getList('catalog_product', $searchCriteria)->getItems();

        foreach ($attributes as $attribute) {
            if ($attribute->getFrontendLabel()) {
                $options[] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontendLabel()
                ];
            }
        }

        return $options;
    }
}
