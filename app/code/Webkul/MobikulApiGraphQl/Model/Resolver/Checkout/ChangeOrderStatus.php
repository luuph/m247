<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphql
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Checkout;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;

/**
 * ChangeOrderStatus resolver
 */
class ChangeOrderStatus extends AbstractCheckout implements ResolverInterface
{
    /**
     * TransactionId variable
     *
     * @var mixed
     */
    protected $transactionId;

    /**
     * State variable
     *
     * @var mixed
     */
    protected $state;

    /**
     * Status variable
     *
     * @var mixed
     */
    protected $status;

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->wholeData = $args;
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $order = $this->orderFactory->create()->loadByIncrementId($this->incrementId);
            if ($order->getId() == "") {
                $this->returnArray["message"] = __("Invalid Order.");
                return $this->returnArray;
            }
            $payment = $order->getPayment();
            $payment->setTransactionId($this->transactionId)
                ->setPreparedMessage("status : ".$this->state)
                ->setShouldCloseParentTransaction(true)
                ->setIsTransactionClosed(0)
                ->registerCaptureNotification($order->getGrandTotal());
            $order->save();
            $state = "";
            if ($this->status == 0) {
                $order->setState(Order::STATE_PROCESSING)
                    ->setStatus(Order::STATE_PROCESSING)
                    ->save();
                $state = Order::STATE_PROCESSING;
            } else {
                $order->setState(Order::STATE_CANCELED)
                    ->setStatus(Order::STATE_CANCELED)
                    ->save();
                $state = Order::STATE_CANCELED;
            }
            if ($order->canInvoice()) {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $transactionSave = $this->dbTransaction->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();
                $this->invoiceSender->send($invoice);
            }
            $comment = "status :".$this->state."<br>";
            $comment .= "transaction id :".$this->transactionId."<br>";
            $order->addStatusHistoryComment($comment)->setIsCustomerNotified(true);
            $order->save();
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * Function to verify request
     *
     * @return void|json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->status = $this->wholeData["status"] ?? 0;
            $this->transactionId = $this->wholeData["transactionId"] ?? "";
            $this->state = $this->wholeData["state"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->incrementId = $this->wholeData["incrementId"] ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerToken && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
