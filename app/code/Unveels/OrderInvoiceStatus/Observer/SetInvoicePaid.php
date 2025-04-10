<?php
namespace Unveels\OrderInvoiceStatus\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order;

class SetInvoicePaid implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/SetInvoicePaid.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('SetInvoicePaid Observer Triggered');

        $order = $observer->getEvent()->getOrder();

        $logger->info('Order ID: ' . $order->getId());
        $logger->info('Order Status: ' . $order->getStatus());

        // Check if order is moving to "Complete"
        if ($order->getState() === Order::STATE_COMPLETE) {
            $logger->info('Order moved to COMPLETE, updating invoice state to PAID');

            foreach ($order->getInvoiceCollection() as $invoice) {
                if ($invoice->getState() != Invoice::STATE_PAID) {
                    $invoice->setState(Invoice::STATE_PAID);
                    $invoice->save();
                    $logger->info('Updated Invoice ID ' . $invoice->getId() . ' to PAID');
                }
            }
        } else {
            $logger->info('Order is not COMPLETE, skipping invoice update.');
        }
    }
}
