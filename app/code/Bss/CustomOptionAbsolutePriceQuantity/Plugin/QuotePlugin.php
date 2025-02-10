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
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface as CatalogItemInterface;

class QuotePlugin
{
    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var ModuleHelper
     */
    private $moduleHelper;

    /**
     * QuotePlugin constructor.
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
     * @param \Magento\Catalog\Helper\Product\Configuration $subject
     * @param mixed $result
     * @param CatalogItemInterface $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCustomOptions(
        \Magento\Catalog\Helper\Product\Configuration $subject,
        $result,
        CatalogItemInterface $item
    ) {
        if ($item->getProduct()->getOptions() && $this->moduleConfig->isModuleEnable()) {
            $orderOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            return $this->moduleHelper->addCoapInfo(
                $result,
                $orderOptions,
                $item->getProduct(),
                $item->getTaxPercent(),
                $item->getQty()
            );
        }
        return $result;
    }
}
