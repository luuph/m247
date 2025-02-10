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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Override\Catalog\Ui\DataProvider\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Catalog\Ui\DataProvider\Product\ProductCustomOptionsDataProvider as CoreCustomDataProvider;
use Magento\Framework\DataObject;

/**
 * Class ProductCustomOptionsDataProvider
 * @package Bss\CustomOptionAbsolutePriceQuantity\Override\Catalog\Ui\DataProvider\Product
 * @codingStandardsIgnoreFile
 */
class ProductCustomOptionsDataProvider extends CoreCustomDataProvider
{
    /**
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $currentProductId = (int)$this->request->getParam('current_product_id');

            if (0 !== $currentProductId) {
                $this->getCollection()->getSelect()->where('e.entity_id != ?', $currentProductId);
            }

            $this->getCollection()->getSelect()->distinct()->join(
                ['opt' => $this->getCollection()->getTable('catalog_product_option')],
                'opt.product_id = e.entity_id',
                null
            );
            $this->getCollection()->load();

            /** @var ProductInterface $product */
            foreach ($this->getCollection() as $product) {
                $options = [];

                /** @var ProductOption|DataObject $option */
                foreach ($this->productOptionRepository->getProductOptions($product) as $option) {
                    $option->setData(
                        'values',
                        $this->productOptionValueModel->getValuesCollection($option)->toArray()['items']
                    );

                    //change from $option->toArray() to $option->getData()  : get bss_tier_price_option
                    $options[] = $option->getData();
                }

                $product->setOptions($options);
            }
        }

        $items = $this->getCollection()->toArray();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }
}
