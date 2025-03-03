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
 * @package   Bss_OneStepCheckout
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OneStepCheckout\Plugin;

use Magento\Framework\Exception\LocalizedException;

class AddDeliveryToSaleOrderBlock
{
    protected $listAddDeliveryDate = ['sales.order.info', 'order_shipping_view'];

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Bss\OneStepCheckout\Helper\Data
     */
    protected $oscHelper;

    /**
     * @var \Bss\OneStepCheckout\Helper\Config
     */
    protected $configHelper;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Bss\OneStepCheckout\Helper\Data $oscHelper
     * @param \Bss\OneStepCheckout\Helper\Config $configHelper
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Bss\OneStepCheckout\Helper\Data        $oscHelper,
        \Bss\OneStepCheckout\Helper\Config      $configHelper
    ) {
        $this->layout = $layout;
        $this->oscHelper = $oscHelper;
        $this->configHelper = $configHelper;
    }

    /**
     * Set delivery date to My Account/Order-Shipment-Invoice
     * Set delivery date to Admin Sales Order/Shipping & Handling Information
     *
     * @param \Magento\Sales\Block\Order\Info|\Magento\Sales\Block\Adminhtml\Order\View\Info $subject
     * @param string $result
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml($subject, $result)
    {
        if ($this->configHelper->isEnabled() &&
            !$this->oscHelper->isModuleInstall('Bss_OrderDeliveryDate') &&
            in_array($subject->getNameInLayout(), $this->listAddDeliveryDate)
        ) {
            $order = $subject->getOrder();
            $deliveryBlock = $this->layout->createBlock(\Magento\Framework\View\Element\Template::class);
            $date = $this->oscHelper->formatDateTime($order);
            $deliveryBlock->setShippingArrivalDate($date)
                ->setShippingArrivalComments($order->getShippingArrivalComments())
                ->setActiveJs(false)
                ->setTemplate('Bss_OneStepCheckout::delivery.phtml');

            if ($subject->getNameInLayout() == 'order_shipping_view') {
                $deliveryBlock->setActiveJs(true);
            }

            $result .= $deliveryBlock->toHtml();
        }

        return $result;
    }
}