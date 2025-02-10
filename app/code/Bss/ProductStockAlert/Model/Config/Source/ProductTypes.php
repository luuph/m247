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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Model\Config\Source;

use Magento\Catalog\Api\Data\ProductTypeInterfaceFactory;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;

class ProductTypes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var ProductTypeInterfaceFactory
     */
    protected $productTypeFactory;

    /**
     * Attribute Position Constructor.
     *
     * @param ConfigInterface $productTypeConfig
     * @param ProductTypeInterfaceFactory $productTypeFactory
     */
    public function __construct(
        ConfigInterface $productTypeConfig,
        ProductTypeInterfaceFactory $productTypeFactory
    ) {
        $this->productTypeConfig = $productTypeConfig;
        $this->productTypeFactory = $productTypeFactory;
    }

    /**
     * Get all product type.
     *
     * @return array
     */
    public function getProductTypes()
    {
        $productTypes = [];
        $notSupport = ['grouped', 'bundle'];
        foreach ($this->productTypeConfig->getAll() as $productTypeData) {
            if (!in_array($productTypeData['name'], $notSupport)) {
                $productType = $this->productTypeFactory->create();
                $productType->setName($productTypeData['name'])
                    ->setLabel($productTypeData['label']);
                $productTypes[] = $productType;
            }
        }

        return $productTypes;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];

        foreach ($this->getProductTypes() as $key => $productType) {
            $arr[$key]['value'] = $productType->getName();
            $arr[$key]['label'] = $productType->getLabel();
        }

        return $arr;
    }
}
