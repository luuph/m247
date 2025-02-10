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
namespace Bss\OrderDeliveryDate\Plugin;

use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo\Total;
use Magento\Sales\Model\Order\Creditmemo;
use Bss\OrderDeliveryDate\Helper\Data;

/**
 * Class UpdateGrandTotal
 *
 * @package Bss\OrderDeliveryDate\Plugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class UpdateGrandTotal extends Total\AbstractTotal
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
     * @var RequestHttp
     */
    protected $request;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Data
     */
    protected $deliveryDateData;

    /**
     * Function construct
     *
     * @param RequestHttp $request
     * @param OrderRepositoryInterface $orderRepository
     * @param Data $deliveryDateData
     * @param array $data
     */
    public function __construct(
        RequestHttp $request,
        OrderRepositoryInterface $orderRepository,
        Data $deliveryDateData,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->request = $request;
        $this->deliveryDateData = $deliveryDateData;
        parent::__construct($data);
    }

    /**
     * Function afterCollect
     *
     * @param $subject
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterCollect($subject, $result, Creditmemo $creditmemo)
    {
        $action = $this->request->getFullActionName();
        if ($action == self::SALES_ORDER_CREDIT_MEMO_NEW || $action == self::SALES_ORDER_CREDIT_MEMO_UPDATE_QTY) {
            $data = $this->request->getPost('creditmemo');
            $order_id = $this->request->getParam('order_id');
            $order = $this->orderRepository->get($order_id);
            $grandTotal = $creditmemo->getGrandTotal();
            $baseGrandTotal = $creditmemo->getBaseGrandTotal();
            $timeSlotPrice = $order->getTimeSlotPrice();
            $baseTimeSlotPrice = $order->getBaseTimeSlotPrice();
            $allowPrice = $baseTimeSlotPrice - $order->getBaseDeliveryTimeSlotPriceRefunded();
            if (isset($data['delivery_time_slot_price']) && $data['delivery_time_slot_price'] != $timeSlotPrice) {
                $formatPrice = $order->getBaseCurrency()->format($allowPrice, null, false);
                if (!is_numeric($data['delivery_time_slot_price'])) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __(
                            'Please enter a number greater than 0 and maximum %1 in delivery time slot field',
                            $formatPrice
                        )
                    );
                }
                if ($data['delivery_time_slot_price'] <= $allowPrice) {
                    $baseGrandTotal += $data['delivery_time_slot_price'];
                    $creditmemo->setBaseGrandTotal($baseGrandTotal);
                    $convertTimeSlotPrice = $this->deliveryDateData
                        ->convertCurrency($data['delivery_time_slot_price'], $order->getOrderCurrencyCode());
                    $creditmemo->setGrandTotal($grandTotal + (double)$convertTimeSlotPrice);
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Maximum delivery time slot price allowed to refund is: %1', $formatPrice)
                    );
                }
            } else {
                $grandTotal += $timeSlotPrice - $order->getDeliveryTimeSlotPriceRefunded();
                $baseGrandTotal += $baseTimeSlotPrice - $order->getBaseDeliveryTimeSlotPriceRefunded();
                $creditmemo->setGrandTotal($grandTotal);
                $creditmemo->setBaseGrandTotal($baseGrandTotal);
            }
        }
        return $result;
    }
}
