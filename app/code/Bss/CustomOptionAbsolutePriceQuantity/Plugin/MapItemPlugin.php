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
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory as QuoteDetailsInterface;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

class MapItemPlugin
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
     * MapItemPlugin constructor.
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
     * @param CommonTaxCollector $subject
     * @param mixed $result
     * @param QuoteDetailsInterface $itemDataObjectFactory
     * @param AbstractItem $item
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @param string $parentCode
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterMapItem(
        CommonTaxCollector $subject,
        $result,
        QuoteDetailsInterface $itemDataObjectFactory,
        AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ) {
        if ($this->moduleConfig->isModuleEnable()) {
            $coapData = $this->moduleHelper->getCoapData($item, $useBaseCurrency);
            $result->setUnitPrice($coapData['unit_price'])->setAbsoluteAmount($coapData['absolute_amount']);
        }
        return $result;
    }
}
