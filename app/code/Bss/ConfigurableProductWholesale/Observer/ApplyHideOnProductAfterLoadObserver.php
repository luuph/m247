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
 * @category  BSS
 * @package   Bss_ConfigurableProductWholesale
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableProductWholesale\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ApplyHideOnProductAfterLoadObserver implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Bss\ConfigurableProductWholesale\Helper\Data
     */
    protected $helper;

    /**
     * ApplyHideOnProductAfterLoadObserver constructor.
     *
     * @param \Bss\ConfigurableProductWholesale\Helper\Data $helper
     */
    public function __construct(
        \Bss\ConfigurableProductWholesale\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Execute
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($this->helper->isModuleEnabled()
            && $this->helper->checkCustomer('hide_price')
            && $product->getTypeId() == 'configurable'
        ) {
            $product->setCanShowPrice(false);
        }
        return $this;
    }
}
