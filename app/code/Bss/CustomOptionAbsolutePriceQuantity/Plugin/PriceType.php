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

class PriceType
{
    const ABSOLUTE_PRICETYPE = 'abs';
    const PERCENT_PRICETYPE = 'percent';

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * PriceType constructor.
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        ModuleConfig $moduleConfig
    ) {
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param \Magento\Catalog\Model\Config\Source\Product\Options\Price $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToOptionArray(\Magento\Catalog\Model\Config\Source\Product\Options\Price $subject, $result)
    {
        if ($this->moduleConfig->isModuleEnable()) {
            $result[] = ['value' => static::ABSOLUTE_PRICETYPE, 'label' => __('Absolute')];
        }
        return $result;
    }
}
