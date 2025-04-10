<?php

namespace Unveels\CitiesGraphqlApi\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use MagePsycho\RegionCityPro\Model\CityFactory;

class GetCitiesByRegion implements ResolverInterface
{
    protected $cityFactory;

    public function __construct(
        CityFactory $cityFactory
    ) {
        $this->cityFactory = $cityFactory;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['region_id']) || !is_int($args['region_id'])) {
            throw new GraphQlInputException(__('Region id must be a valid integer.'));
        }
    
        $regionId = $args['region_id'];
        $cityModel = $this->cityFactory->create();
        $cityCollection = $cityModel->getCollection()
            ->addFieldToFilter('region_id', $regionId);
    
        $cities = [];
        foreach ($cityCollection as $city) {
            $cities[] = [
                'city_id' => $city->getId(),
                'city_name' => $city->getName(),
            ];
        }
        // print_r($cities);exit;
        return $cities;
    }
}
