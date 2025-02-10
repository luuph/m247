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

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig;
use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleHelper;

class OrderItemObserver implements ObserverInterface
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
     * OrderItemObserver constructor.
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
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $item = $observer->getItem();
        if ($this->moduleConfig->isModuleEnable() && !array_key_exists('coap_info', $item->getProductOptions())
            && array_key_exists('options', $item->getProductOptions()) && $item->getProductOptions() !== null
        ) {
            $orderOptions = $item->getProductOptions();
            $orderOptions['options'] = $this->moduleHelper->addCoapInfo(
                $orderOptions['options'],
                $orderOptions,
                $item->getProduct(),
                $item->getTaxPercent(),
                $item->getQty()
            );
            $orderOptions['coap_info'] = true;
            $item->setProductOptions($orderOptions);
        }
    }
}
