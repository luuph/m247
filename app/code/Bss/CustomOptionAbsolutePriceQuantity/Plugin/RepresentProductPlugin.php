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
namespace Bss\CustomOptionAbsolutePriceQuantity\Plugin;

use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig;
use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleHelper;
use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Model\Product;

class RepresentProductPlugin
{
    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var ModuleHelper
     */
    protected $moduleHelper;

    /**
     * RepresentProductPlugin constructor.
     * @param ModuleConfig $moduleConfig
     * @param ModuleHelper $moduleHelper
     */
    public function __construct(
        ModuleConfig $moduleConfig,
        ModuleHelper $moduleHelper
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * @param Item $subject
     * @param mixed $result
     * @param Product $product
     * @return bool
     */
    public function afterRepresentProduct(
        Item $subject,
        $result,
        $product
    ) {
        if ($result && $this->moduleConfig->isModuleEnable()) {
            $newInfo = $product->getTypeInstance(true)->getOrderOptions($product);
            $newOptionQty = (array_key_exists('option_qty', $newInfo['info_buyRequest'])) ?
            $newInfo['info_buyRequest']['option_qty'] : [];

            $itemInfo = $subject->getProduct()->getTypeInstance(true)->getOrderOptions($subject->getProduct());
            $itemOptionsQty = (array_key_exists('option_qty', $itemInfo['info_buyRequest'])) ?
            $itemInfo['info_buyRequest']['option_qty'] : [];
            return $newOptionQty == $itemOptionsQty;
        }
        return $result;
    }
}
