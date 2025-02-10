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
namespace Bss\OrderDeliveryDate\Controller\Adminhtml\Order;

class DeliverySave extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Bss\OrderDeliveryDate\Helper\Data
     */
    protected $helperBss;

    /**
     * DeliverySave constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Sales\Model\Order $order
     * @param \Bss\OrderDeliveryDate\Helper\Data $helperBss
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Bss\OrderDeliveryDate\Helper\Data $helperBss
    ) {
        parent::__construct($context);
        $this->order = $order;
        $this->helperBss = $helperBss;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        preg_match("/\((\+\D*)([0-9,]*\.[0-9]*)(\D)?\)$/", $params['shipping_arrival_timeslot'], $matches);
        $timeSlotPrice = $matches[2] ?? 0;
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($params['order_id']) {
            $orderInfo = $this->order->load($params['order_id']);
            $this->updateGrandTotal($timeSlotPrice, $orderInfo);
            if ($this->helperBss->isShowShippingComment()) {
                $orderInfo->setShippingArrivalDate($params['shipping_arrival_date'])
                    ->setShippingArrivalTimeslot($params['shipping_arrival_timeslot'])
                    ->setShippingArrivalComments($params['shipping_arrival_comments'])
                    ->save();
            } else {
                $orderInfo->setShippingArrivalDate($params['shipping_arrival_date'])
                    ->setShippingArrivalTimeslot($params['shipping_arrival_timeslot'])
                    ->save();
            }
            return $resultRedirect->setPath('sales/*/view', ['order_id' => $params['order_id']]);
        } else {
            return $resultRedirect->setPath('sales/*/');
        }
    }

    /**
     * Update grand total and time slot price
     *
     * @param $timeSlotPrice
     * @param $orderInfo
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateGrandTotal($timeSlotPrice, $order)
    {
        $timeSlotPrice =  str_replace(',', '', $timeSlotPrice);
        $baseTimeSlotPrice = round($this->helperBss->convertToBaseCurrency($timeSlotPrice));
        $order->setGrandTotal((double)$order->getGrandTotal() - (double)$order->getTimeSlotPrice() + (double)$timeSlotPrice)
            ->setBaseGrandTotal((double)$order->getBaseGrandTotal() - (double)$order->getBaseTimeSlotPrice() + $baseTimeSlotPrice)
            ->setTimeSlotPrice($timeSlotPrice)
            ->setBaseTimeSlotPrice($baseTimeSlotPrice)
            ->save();
    }
}
