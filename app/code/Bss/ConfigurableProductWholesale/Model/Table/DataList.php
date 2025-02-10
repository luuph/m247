<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ConfigurableProductWholesale
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableProductWholesale\Model\Table;

use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;

class DataList
{
    /**
     * @var ConfigurableProductType
     */
    private $configurableProductType;

    /**
     * @var string[]
     */
    private $dataList;

    /**
     * @var ConfigurableAttributeData
     */
    protected $configurableAttributeData;

    /**
     * DataList constructor.
     * @param ConfigurableProductType $configurableProductType
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param array $dataList
     */
    public function __construct(
        ConfigurableProductType $configurableProductType,
        ConfigurableAttributeData $configurableAttributeData,
        array $dataList
    ) {
        $this->configurableProductType = $configurableProductType;
        $this->configurableAttributeData = $configurableAttributeData;
        $this->dataList = $dataList;
    }

    /**
     * @param $productCollection
     * @return mixed
     */
    public function prepareCollection($productCollection)
    {
        foreach ($this->dataList as $dataModel) {
            $dataModel->prepareCollection($productCollection);
        }
        return $productCollection;
    }

    /**
     * Get product data.
     *
     * @param mixed $productCollection
     * @return array
     */
    public function getData($productCollection)
    {
        $data = [];
        foreach ($this->dataList as $dataName) {
            $data[] = $dataName->getData($productCollection);
        }
        $productData = [];
        foreach ($data as $dataName) {
            foreach ($dataName as $key => $value) {
                if (!array_key_exists('CPWD' . $key, $productData)) {
                    $productData['CPWD' . $key] = $value;
                } else {
                    foreach ($value as $id => $data) {
                        $productData['CPWD' . $key][$id] = $data;
                    }
                }
            }
        }
        return $productData;
    }
}
