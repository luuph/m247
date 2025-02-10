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
namespace Bss\CustomOptionAbsolutePriceQuantity\Observer\Adminhtml\Render;

use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig;
use \Magento\Framework\Event\ObserverInterface;

class AddAbsoluteTooltip implements ObserverInterface
{
    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

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
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->moduleConfig->isModuleEnable()) {
            $observer->getChild()->addData(
                ['coap_tooltip' => \Bss\CustomOptionAbsolutePriceQuantity\Block\Adminhtml\Render\OrderOptionTip::class]
            );
        }
    }
}
