<?php

namespace MagePsycho\RegionCityPro\Model\ResourceModel\Address\Attribute\Source;

use MagePsycho\RegionCityPro\Model\ResourceModel\City\CollectionFactory as CityCollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class City extends AbstractSource
{
    /**
     * @var CityCollectionFactory
     */
    private $cityCollectionFactory;

    public function __construct(
        CityCollectionFactory $cityCollectionFactory
    ) {
        $this->cityCollectionFactory = $cityCollectionFactory;
    }

    public function getAllOptions($withEmpty = false)
    {
        if ($this->_options === null) {
            $this->_options = $this->cityCollectionFactory->create()->load()->toOptionArray();
        }

        if ($withEmpty) {
            array_unshift($this->_options, ['value' => '', 'label' => '']);
        }

        return $this->_options;
    }

    public function getOptionsArray($withEmpty = true)
    {
        $options = [];
        foreach ($this->getAllOptions($withEmpty) as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    public function getOptionText($value)
    {
        $options = $this->getAllOptions(false);
        foreach ($options as $item) {
            if ($item['value'] == $value) {
                return $item['label'];
            }
        }
        return false;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    public function toOptionHash($withEmpty = true)
    {
        return $this->getOptionsArray($withEmpty);
    }
}
