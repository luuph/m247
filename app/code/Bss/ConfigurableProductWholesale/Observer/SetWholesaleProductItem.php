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

class SetWholesaleProductItem implements ObserverInterface
{
    /**
     * @var \Bss\ConfigurableProductWholesale\Helper\Data
     */
    protected $helperBss;

    /**
     * @param \Bss\ConfigurableProductWholesale\Helper\Data $helperBss
     */
    public function __construct(
        \Bss\ConfigurableProductWholesale\Helper\Data $helperBss
    ) {
        $this->helperBss = $helperBss;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $status = false;
        $product = $observer->getEvent()->getProduct();
        if ($this->isEnabledCpwd($product)) {
            $status = true;
        }
        $product->setEnabledCpwd($status);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    private function isEnabledCpwd($product)
    {
        return $product->getTypeId() === 'configurable'
            && $this->helperBss->isModuleEnabled()
            && $this->isEnableCustomerGroup()
            && $this->isEnableCpwd($product);
    }

    /**
     * @return bool
     */
    private function isEnableCustomerGroup()
    {
        return $this->helperBss->checkCustomer('active_customer_groups');
    }

    /**
     * @param $product
     * @return bool
     */
    private function isEnableCpwd($product)
    {
        $isEnabled = false;
        if ($product->getEnableCpwd() || $product->getEnableCpwd() == null) {
            $isEnabled = true;
        }
        return $isEnabled;
    }
}
