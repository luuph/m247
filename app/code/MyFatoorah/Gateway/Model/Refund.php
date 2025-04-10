<?php

namespace MyFatoorah\Gateway\Model;

use Exception;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use MyFatoorah\Gateway\Model\MyfatoorahInvoice;
use MyFatoorah\Gateway\Model\ResourceModel\MyfatoorahInvoice\CollectionFactory;
use MyFatoorah\Library\API\MyFatoorahRefund;

class Refund extends AbstractMethod implements HandlerInterface
{

    /**
     * @var CollectionFactory
     */
    public $mfInvoiceFactory;

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param CollectionFactory $mfInvoiceFactory
     */
    public function __construct(
            Context $context,
            Registry $registry,
            ExtensionAttributesFactory $extensionFactory,
            AttributeValueFactory $customAttributeFactory,
            Data $paymentData,
            ScopeConfigInterface $scopeConfig,
            Logger $logger,
            CollectionFactory $mfInvoiceFactory
    ) {
        parent::__construct(
                $context,
                $registry,
                $extensionFactory,
                $customAttributeFactory,
                $paymentData,
                $scopeConfig,
                $logger
        );
        $this->mfInvoiceFactory = $mfInvoiceFactory;
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     *
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var Magento\Sales\Model\Order\Payment\Interceptor $payment */
        $payment = $handlingSubject['payment']->getPayment();

        /** @var Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $payment->getCreditmemo(); //$payment->getData('creditmemo') or $payment->getData()['creditmemo']

        /** @var Magento\Sales\Model\Order\Invoice\Interceptor $magInvoice */
        $magInvoice = $creditmemo->getData('invoice');
        if (!$magInvoice) {
            $exMsg = __('MyFatoorah Refund: can\'t refund transaction because there is no capture transaction.');
            throw new LocalizedException($exMsg);
        }

        $invoiceId = $magInvoice->getData('transaction_id');
        $orderId   = $magInvoice->getData('order_id');

        $mfInvoice      = $this->getMyFatoorahInvoiceItem($invoiceId, $orderId);
        $portalCurrency = $mfInvoice->getPortalCurrency();

        $comments = $creditmemo->getComments();
        $comment  = empty($comments[0]) ? '' : $comments[0]->getComment();

        try {
            $order    = $payment->getOrder();
            $mfConfig = $this->getMyfatoorahScopeConfig($order);

            list($displayAmount, $displayCurrency) = $this->getAmountBasedOnConfig($creditmemo, $mfConfig['configCurrency']);

            $postFields = [
                'Key'                     => $invoiceId,
                'KeyType'                 => 'InvoiceId',
                'RefundChargeOnCustomer'  => false,
                'ServiceChargeOnCustomer' => false,
                'Amount'                  => "$displayAmount",
                'CurrencyIso'             => $displayCurrency,
                'Comment'                 => $comment,
            ];

            $mfObj = new MyFatoorahRefund($mfConfig);
            $data  = $mfObj->makeRefund($postFields, $orderId);

            //add refund to myfatoorah
            $data->Currency        = $portalCurrency;
            $data->DisplayAmount   = $displayAmount;
            $data->DisplayCurrency = $displayCurrency;
            $this->addRefundDataToMfInvoice($mfInvoice, $data);
            
            //add refund to magento2
            $creditmemo->setState(Creditmemo::STATE_OPEN);
            $creditmemo->save();

            $refundTransactionId = 'mf-' . $invoiceId . '-refund-' . $data->RefundId;
            $payment->setTransactionId($refundTransactionId);
            $payment->setRefundTransactionId($data->RefundInvoiceId);
            $payment->save();

            $this->addNoteToOrder($order, $data, $mfConfig['configCurrency']);
        } catch (Exception $ex) {
            $msg = sprintf('MyFatoorah Refund: %s.', $ex->getMessage());
            throw new LocalizedException(__($msg));
        }
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get MyFatoorahRefund Object
     * 
     * @param Order $order
     * 
     * @return array
     */
    private function getMyfatoorahScopeConfig($order)
    {
        $storeId = $order->getStoreId();
        $path    = 'payment/myfatoorah_payment/';
        $scope   = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return [
            'apiKey'         => $this->_scopeConfig->getValue($path . 'api_key', $scope, $storeId),
            'isTest'         => (bool) $this->_scopeConfig->getValue($path . 'is_testing', $scope, $storeId),
            'countryCode'    => $this->_scopeConfig->getValue($path . 'countryMode', $scope, $storeId),
            'loggerObj'      => MYFATOORAH_LOG_FILE,
            'configCurrency' => $this->_scopeConfig->getValue($path . 'invoiceCurrency', $scope, $storeId)
        ];
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Check the MyFatoorah configuration to get the display invoice currency for refund
     *
     * @param Creditmemo $creditmemo
     *
     * @return array
     */
    private function getAmountBasedOnConfig($creditmemo, $configCurrency)
    {
        if ($configCurrency == 'websites') {
            $displayAmount   = $creditmemo->getData('grand_total');
            $displayCurrency = $creditmemo->getData('order_currency_code');
        } else {
            $displayAmount   = $creditmemo->getData('base_grand_total');
            $displayCurrency = $creditmemo->getData('base_currency_code');
        }
        return [$displayAmount, $displayCurrency];
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the MyFatoorah invoice data
     *
     * @param string $invoiceId
     * @param string $orderId
     *
     * @return MyfatoorahInvoice
     *
     * @throws LocalizedException
     */
    private function getMyFatoorahInvoiceItem($invoiceId, $orderId)
    {
        //get the item
        $collection = $this->mfInvoiceFactory->create()->addFieldToFilter('invoice_id', $invoiceId);

        $item = $collection->fetchItem();
        if (!$item || ($item->getInvoiceId() != $invoiceId && $item->getOrderId() != $orderId)) {
            $msg = "MyFatoorah Refund: can't find the invoice $invoiceId in the DB";
            throw new LocalizedException(__($msg));
        }

        return $item;
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Save MyFatoorah Refund Data into DB
     *
     * @param MyfatoorahInvoice $mfInvoice
     * @param Object $data
     */
    private function addRefundDataToMfInvoice($mfInvoice, $data)
    {
        $refundData = $mfInvoice->getRefundData() ?: '';
        $refundArr  = json_decode($refundData, true);

        $refundArr[$data->RefundId] = $data;

        $mfInvoice->setData('refund_data', json_encode($refundArr));
        $mfInvoice->save();
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Add the MyFatoorah refund note to the order
     *
     * @param Order $order
     * @param Object $data
     */
    private function addNoteToOrder($order, $data, $configCurrency)
    {
        $note = '<b>MyFatoorah Refund Details:</b><br>';

        //add the note
        $displayTotal = ($configCurrency == 'websites') ? $order->getGrandTotal() : $order->getBaseGrandTotal();
        if ($data->DisplayAmount < $displayTotal) {
            $note .= 'Refund request is partial<br>';
        }

        $note .= 'RefundStatus: PENDING<br>';
        $note .= 'RefundId: ' . $data->RefundId . '<br>';
        $note .= 'RefundReference: ' . $data->RefundReference . '<br>';
        $note .= 'RefundInvoiceId: ' . $data->RefundInvoiceId . '<br>';

        $note .= 'PortalAmount: ' . $data->Amount . ' ' . $data->Currency . '<br>';
        $note .= 'DisplayAmount: ' . $data->DisplayAmount . ' ' . $data->DisplayCurrency . '<br>';

        $note .= 'Comment: ' . $data->Comment . '<br>';

        $order->addStatusHistoryComment($note);
        $order->save();
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------
}
