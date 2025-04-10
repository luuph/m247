<?php

namespace MyFatoorah\Gateway\Model;

use DateTime;
use Exception as MFException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Store\Model\ScopeInterface;
use MyFatoorah\Gateway\Controller\Checkout\Success;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;
use MyFatoorah\Library\MyFatoorah;

class WebHook
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Success
     */
    private $successObj;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var MyfatoorahInvoice
     */
    private $mfInvoice;

    /**
     * @var string
     */
    private $logger;

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     *
     * @param Order $order
     * @param Success $successObj
     * @param MyfatoorahInvoice $mfInvoice
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
            Order $order,
            Success $successObj,
            MyfatoorahInvoice $mfInvoice,
            ScopeConfigInterface $scopeConfig
    ) {
        $this->order       = $order;
        $this->mfInvoice   = $mfInvoice;
        $this->successObj  = $successObj;
        $this->scopeConfig = $scopeConfig;

        $this->logger = BP . '/var/log/myfatoorah_webhook.log';
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Load the MyFatoorah invoice using the invoice id
     *
     * @param type $invoiceId
     */
    private function loadInvoice($invoiceId)
    {
        $collection = $this->mfInvoice->getCollection()->addFieldToFilter('invoice_id', $invoiceId);

        $this->mfInvoice = $collection->fetchItem();
        if (!$this->mfInvoice) {
            throw new MFException('Wrong invoice.');
        }
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Load the order using the increment id
     *
     * @param type $orderId
     */
    private function loadOrder($orderId)
    {
        $this->order->loadByIncrementId($orderId);
        if (!$this->order->getId()) {
            throw new MFException('Wrong order.');
        }
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function execute($eventType, $event, $data)
    {
        error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - Webhook - ' . $event, 3, $this->logger);
        error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - Webhook - Data ' . json_encode($data), 3, $this->logger);
        try {
            //check for request
            if (!$data || !in_array($eventType, [1, 2])) {
                throw new MFException('Wrong request.');
            }

            //check for MyFatoorah Signature from request headers
            $apache  = apache_request_headers();
            $headers = array_change_key_case($apache);
            if (empty($headers['myfatoorah-signature'])) {
                throw new MFException('Wrong headers.');
            }

            $this->loadInvoice($data['InvoiceId']);
            $this->loadOrder($this->mfInvoice->getOrderId());

            //get the order store config
            $scope   = ScopeInterface::SCOPE_STORE;
            $storeId = $this->order->getStoreId();
            $path    = 'payment/myfatoorah_payment/';

            $secretKey = $this->scopeConfig->getValue($path . 'webhookSecretKey', $scope, $storeId);
            if (!$secretKey) {
                throw new MFException('Wrong configuration.');
            }

            //validate signature
            if (!MyFatoorah::isSignatureValid($data, $secretKey, $headers['myfatoorah-signature'], $eventType)) {
                throw new MFException('Wrong signature.');
            }

            //to allow the callback code run 1st for transactionsStatusChanged.
            //to allow the save refund data b4 auto refund.
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            sleep(5);

            //reload the invoice to be sure its updated
            $this->loadInvoice($data['InvoiceId']);

            $info = $this->{lcfirst($event)}($data);

            $msg = 'Success.' . ($info ? ' ' . $info : '');
        } catch (\Exception $ex) {
            $msg = 'Exception: ' . $ex->getMessage();
        }

        error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - Webhook - ' . $msg, 3, $this->logger);
        return $msg;
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Change Order Status due to the Transactions Status Changed
     *
     * @param  array $data
     *
     * @return string
     */
    private function transactionsStatusChanged($data)
    {
        //order is not pending or canceled
        $status = $this->order->getState();
        if ($status !== Order::STATE_PENDING_PAYMENT && $status !== Order::STATE_CANCELED) {
            return false;
        }

        //get the order store config
        $scope   = ScopeInterface::SCOPE_STORE;
        $storeId = $this->order->getStoreId();

        $path = 'payment/myfatoorah_payment/';

        $config = [
            'apiKey'      => $this->scopeConfig->getValue($path . 'api_key', $scope, $storeId),
            'isTest'      => (bool) $this->scopeConfig->getValue($path . 'is_testing', $scope, $storeId),
            'countryCode' => $this->scopeConfig->getValue($path . 'countryMode', $scope, $storeId),
            'loggerObj'   => $this->logger
        ];

        $mfObj   = new MyFatoorahPaymentStatus($config);
        $orderId = $this->mfInvoice->getOrderId();
        return $this->successObj->checkStatus($data['InvoiceId'], 'InvoiceId', $mfObj, '-WebHook', false, $orderId);
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Update the Refund request status
     *
     * @param array $data
     *
     * @return string
     */
    private function refundStatusChanged($data)
    {
        //get refund_data
        $refundData = $this->mfInvoice->getRefundData() ?: '';
        $refundArr  = json_decode($refundData, true);

        $refundObj = $refundArr[$data['RefundId']] ?? null;
        if (!$refundObj) {
            throw new MFException('RefundId #' . $data['RefundId'] . ' is not found.');
        }

        $filter = 'mf-' . $data['InvoiceId'] . '-refund-' . $data['RefundId'];

        $collections = $this->order->getCreditmemosCollection();
        $collections->addFieldToFilter('transaction_id', $filter);

        /** @var Creditmemo $creditmemo */
        $creditmemo = $collections->fetchItem();
        if (!$creditmemo) {
            throw new MFException('Creditmemo is not found: ' . $filter);
        }

        $currentState = $creditmemo->getState();
        if ($currentState != Creditmemo::STATE_OPEN) {
            $stateName = Creditmemo::getStates()[$currentState];
            throw new MFException("Creditmemo is already $stateName.");
        }

        if ($data['RefundStatus'] == 'CANCELED') {
            $creditmemo->setState(Creditmemo::STATE_CANCELED);
        } elseif ($data['RefundStatus'] == 'REFUNDED') {
            $creditmemo->setState(Creditmemo::STATE_REFUNDED);
        }
        $creditmemo->setUpdatedAt(time());
        $creditmemo->save();

        //add note to order
        $note = '<b>MyFatoorah Refund Details:</b><br>';

        $note .= 'RefundStatus: ' . $data['RefundStatus'] . '<br>';
        $note .= 'RefundId: ' . $data['RefundId'] . '<br>';
        $note .= 'RefundReference: ' . $data['RefundReference'] . '<br>';

        $createdDate = DateTime::createFromFormat('dmYHis', $data['CreatedDate']);
        $note        .= 'CreatedDate: ' . date_format($createdDate, 'Y-m-d H:i:s') . '<br>';

        $note .= 'PortalAmount: ' . $data['Amount'] . ' ' . $refundObj['Currency'] . '<br>';
        $note .= 'DisplayAmount: ' . $refundObj['DisplayAmount'] . ' ' . $refundObj['DisplayCurrency'] . '<br>';

        $note .= 'Comment: ' . $data['Comments'] . '<br>';

        $this->order->addStatusHistoryComment($note);
        $this->order->save();

        return 'Refund status is ' . $data['RefundStatus'];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
