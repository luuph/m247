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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\OrderDeliveryDate\Observer;

use Bss\OrderDeliveryDate\Helper\Data;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class CreditMemoSaveBefore
 *
 * @package Bss\OrderDeliveryDate\Observer
 */
class CreditMemoSaveBefore implements ObserverInterface
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var $orderRepository
     */
    protected $orderRepository;

    /**
     * @var Data
     */
    protected $deliveryDateData;

    /**
     * Function construct
     *
     * @param Http $request
     * @param OrderRepositoryInterface $orderRepository
     * @param Data $deliveryDateData
     */
    public function __construct(
        Http $request,
        OrderRepositoryInterface $orderRepository,
        Data $deliveryDateData
    ) {
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->deliveryDateData = $deliveryDateData;
    }

    /**
     * Function execute
     *
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $creditmemo = $observer->getCreditmemo();
        $order = $creditmemo->getOrder();
        $creditmemoData = $this->request->getParam('creditmemo');
        if (isset($creditmemoData['delivery_time_slot_price'])) {
            $timeSlotPrice = (double) $this->deliveryDateData
                ->convertCurrency($creditmemoData['delivery_time_slot_price'], $order->getOrderCurrencyCode());
            $creditmemo->setBaseDeliveryTimeSlotPrice($creditmemoData['delivery_time_slot_price']);
            $creditmemo->setDeliveryTimeSlotPrice($timeSlotPrice);
            $grandTotal = $creditmemo->getGrandTotal() + $timeSlotPrice;
            $creditmemo->setGrandTotal($grandTotal);
            $baseGrandTotal = $creditmemo->getBaseGrandTotal() + $creditmemoData['delivery_time_slot_price'];
            $creditmemo->setBaseGrandTotal($baseGrandTotal);
            if ($creditmemoData['do_offline'] == '1') {
                $order->setTotalOfflineRefunded($order->getTotalOfflineRefunded() + $grandTotal);
                $order->setBaseTotalOfflineRefunded($order->getBaseTotalOfflineRefunded() + $baseGrandTotal);
            } else {
                $order->setTotalOnlineRefunded($order->getTotalOnlineRefunded() + $grandTotal);
                $order->setBaseTotalOnlineRefunded($order->getBaseTotalOnlineRefunded() + $baseGrandTotal);
            }
            $order->setBaseTotalRefunded($order->getBaseTotalRefunded() + $creditmemoData['delivery_time_slot_price']);
            $order->setTotalRefunded($order->getTotalRefunded() + $timeSlotPrice);
            $order->setBaseDeliveryTimeSlotPriceRefunded(
                $order->getBaseDeliveryTimeSlotPriceRefunded() + (double)$creditmemoData['delivery_time_slot_price']
            );
            $order->setDeliveryTimeSlotPriceRefunded($order->getDeliveryTimeSlotPriceRefunded() + $timeSlotPrice);
        }
        return $this;
    }
}
