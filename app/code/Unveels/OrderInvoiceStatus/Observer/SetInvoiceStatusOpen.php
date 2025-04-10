<?php
namespace Unveels\OrderInvoiceStatus\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order;

class SetInvoiceStatusOpen implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/SetInvoiceStatusOpen.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('SetInvoiceStatusOpen Observer Triggered');

        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        
        $paymentMethod = $order->getPayment()->getMethod();
        $totalDue = $order->getTotalDue();

        $logger->info('Payment Method: ' . $paymentMethod);
        $logger->info('Total Due: ' . $totalDue);

        if ($paymentMethod === 'cashondelivery' || $totalDue > 0) {
            $logger->info('Inside IF condition: Setting invoice to OPEN');
            $invoice->setState(Invoice::STATE_OPEN);
            $invoice->save();
        } else {
            $logger->info('Skipping: Payment is complete, keeping invoice status unchanged.');
        }
    }
}
