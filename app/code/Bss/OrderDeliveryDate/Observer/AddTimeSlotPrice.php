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

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class AddTimeSlotPrice
 *
 * @package Bss\OrderDeliveryDate\Observer
 */
class AddTimeSlotPrice implements ObserverInterface
{
    /**
     * @var String
     */
    const SALES_ORDER_CREDIT_MEMO_NEW = 'sales_order_creditmemo_new';

    /**
     * @var String
     */
    const SALES_ORDER_CREDIT_MEMO_UPDATE_QTY = 'sales_order_creditmemo_updateQty';

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Http
     */
    protected $request;

    /**
     * Function construct
     *
     * @param Http $request
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Http                     $request,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->request = $request;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Function execute
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->checkNewCreditMemo($observer)) {
            if ($this->checkTimeSlotPrice()) {
                $html = $this->htmlTimeSlotPrice();
                $output = $observer->getTransport()->getOutput() . $html;
                $observer->getTransport()->setOutput($output);
            }
        }
    }

    /**
     * Check new credit memo to add time slot price
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool
     */
    public function checkNewCreditMemo($observer)
    {
        $action = $this->request->getFullActionName();
        if (($action == self::SALES_ORDER_CREDIT_MEMO_NEW || $action == self::SALES_ORDER_CREDIT_MEMO_UPDATE_QTY)
            && $observer->getElementName() == 'adjustments') {
            return true;
        }
        return false;
    }

    /**
     * Check order has time slot price
     *
     * @return bool
     */
    public function checkTimeSlotPrice()
    {
        $orderId = $this->request->getParam("order_id");
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order->getTimeSlotPrice()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Html time slot price
     *
     * @return string
     */
    public function htmlTimeSlotPrice()
    {
        $html = '<tr>
        <td class="label">';
        $html .= __('Delivery Time Slot Price');
        $html .= '<div id="delivery_time_slot_price_adv"></div>
            </td>
            <td>
                <input type="text"
                        name="creditmemo[delivery_time_slot_price]"
                        class="input-text admin__control-text not-negative-amount"
                        id="delivery_time_slot_price"
                        value="';
        $html .= $this->formatValue($this->getTimeSlotPrice());
        $html .= '"/></td></tr>';
        return $html;
    }

    /**
     * Format value based on order currency
     *
     * @param null|float $value
     *
     * @return string
     * @since 102.1.0
     */
    public function formatValue($value)
    {
        $order = $this->getOrder();
        return $order->getOrderCurrency()->formatPrecision(
            $value,
            2,
            ['display' => \Magento\Framework\Currency::NO_SYMBOL],
            false,
            false
        );
    }

    /**
     * Function get order by order id
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        return $this->orderRepository->get($this->request->getParam('order_id'));
    }

    /**
     * Function get time slot price
     *
     * @return mixed
     */
    public function getTimeSlotPrice()
    {
        $data = $this->request->getPost('creditmemo');
        if (isset($data['delivery_time_slot_price'])) {
            return $data['delivery_time_slot_price'];
        } else {
            return $this->getOrder()->getBaseTimeSlotPrice()
                - $this->getOrder()->getBaseDeliveryTimeSlotPriceRefunded();
        }
    }
}
