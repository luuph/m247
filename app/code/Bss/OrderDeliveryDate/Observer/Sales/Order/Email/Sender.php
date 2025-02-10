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
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderDeliveryDate\Observer\Sales\Order\Email;

class Sender implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Bss\OrderDeliveryDate\Helper\Data
     */
    protected $helperData;

    /**
     * Construct
     *
     * @param \Bss\OrderDeliveryDate\Helper\Data $helperData
     */
    public function __construct(
        \Bss\OrderDeliveryDate\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Set some information to Transport Object
     * 
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $transportObject = $observer->getData('transportObject');
        if ($this->helperData->isEnabled() && !empty($transportObject)) {
            $order = $transportObject->getData('order');
            $transportObject->setData('delivery_time_slot', $order->getShippingArrivalTimeslot());
            $transportObject->setData('shipping_arrival_comments', $order->getShippingArrivalComments());
            if ($order->getShippingArrivalDate()) {
                $transportObject->setData('shipping_arrival_date', $this->helperData->formatDate($order->getShippingArrivalDate()));
            }
        }
    }
}
