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
 * @package    Bss_ConfigurableProductWholesale
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableProductWholesale\Plugin\Layout;

class AddHandleProductLayout
{
    /**
     * @var \Bss\ConfigurableProductWholesale\Helper\Data
     */
    private $helperBss;

    /**
     * @param \Bss\ConfigurableProductWholesale\Helper\Data $helperBss
     */
    public function __construct(
        \Bss\ConfigurableProductWholesale\Helper\Data $helperBss
    ) {
        $this->helperBss = $helperBss;
    }

    /**
     * Add layout handle only when module enable
     *
     * @param \Magento\Catalog\Helper\Product\View $subject
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @param \Magento\Catalog\Model\Product $product
     * @param null|\Magento\Framework\DataObject $params
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeInitProductLayout(
        \Magento\Catalog\Helper\Product\View $subject,
        \Magento\Framework\View\Result\Page $resultPage,
        $product,
        $params = null
    ) {
        if ($this->isEnabledCpwd($product)) {
            $resultPage->addHandle('bss_cpwd');
        }
        return [$resultPage, $product, $params];
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
     * @return bool
     */
    public function isEnableCpwd($product)
    {
        $isEnabled = false;
        if ($product->getEnableCpwd() || $product->getEnableCpwd() == null) {
            $isEnabled = true;
        }
        return $isEnabled;
    }
}
