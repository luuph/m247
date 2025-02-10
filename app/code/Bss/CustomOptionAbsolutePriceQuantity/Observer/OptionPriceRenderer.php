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
namespace Bss\CustomOptionAbsolutePriceQuantity\Observer;

use Magento\Framework\Event\ObserverInterface;
use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig;

class OptionPriceRenderer implements ObserverInterface
{
    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * OptionPriceRenderer constructor.
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        ModuleConfig $moduleConfig
    ) {
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $configObj = $observer->getEvent()->getData('configObj');
        $configDataClone = $configObj->getConfig();
        if (!$this->moduleConfig->isModuleEnable() && is_array($configDataClone)) {
            foreach ($configDataClone as &$option) {
                if (isset($option['type']) && $option['type'] == 'abs') {
                    $option['type'] = 'fixed';
                } else {
                    foreach ($option as &$item) {
                        if (isset($item['type']) && $item['type'] == 'abs') {
                            $item['type'] = 'fixed';
                        }
                    }
                }
            }
            $configObj->setConfig($configDataClone);
        }
    }
}
