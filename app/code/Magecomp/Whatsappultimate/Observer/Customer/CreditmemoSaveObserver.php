<?php
namespace Magecomp\Whatsappultimate\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;

class CreditmemoSaveObserver implements ObserverInterface
{
    protected $helperapi;
    protected $helpercreditmemo;
    protected $emailfilter;
    protected $customerFactory;
    protected $customersession;

    public function __construct(
        \Magecomp\Whatsappultimate\Helper\Apicall $helperapi,
        \Magecomp\Whatsappultimate\Helper\Creditmemo $helpercreditmemo,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customersession

    ) {
        $this->helperapi = $helperapi;
        $this->helpercreditmemo = $helpercreditmemo;
        $this->emailfilter = $filter;
        $this->customerFactory = $customerFactory;
        $this->customersession = $customersession;

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/magecomp-creditmemo.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        
        $logger->info('creditmemo : ');
        $creditmemo = $observer->getCreditmemo();
        $order      = $creditmemo->getOrder();
        $storeId    = $order->getStoreId();
        if (!$this->helpercreditmemo->isEnabled($storeId)) {
            return $this;
        }

        if ($creditmemo) {
            $data = [
                'order_id' => (string)$order->getId(),
                'order_state' => (string)$order->getState(),
                'order_status' => (string)$order->getStatus(),
                'order_coupon_code' => (string)$order->getCouponCode(),
                'order_shipping_description' => $order->getShippingDescription(),
                'order_base_grand_total' => (string)$order->getBaseGrandTotal(),
                'order_base_shipping_amount' => (string) $order->getBaseShippingAmount(),
                'order_base_subtotal' => (string)$order->getBaseSubtotal(),
                'order_base_tax_amount' => (string)$order->getBaseTaxAmount(),
                'order_discount_amount' => (string)$order->getDiscountAmount(),
                'order_grand_total' => (string)$order->getGrandTotal(),
                'order_shipping_amount' => (string)$order->getShippingAmount(),
                'order_shipping_tax_amount' => (string)$order->getShippingTaxAmount(),
                'order_subtotal' => (string)$order->getSubtotal(),
                'order_tax_amount' => (string)$order->getTaxAmount(),
                'order_total_qty_ordered' => (string)$order->getTotalQtyOrdered(),
                'order_increment_id' => (string)$order->getIncrementId(),
                'order_customer_email' => (string)$order->getCustomerEmail(),
                'order_customer_firstname' => (string)$order->getCustomerFirstname(),
                'order_customer_lastname' => (string)$order->getCustomerLastname(),
                'order_currency_code' => (string)$order->getOrderCurrencyCode(),
                'order_store_name' => (string)$order->getStoreName(),
                'order_created_at' => (string)$order->getCreatedAt(),
                'order_order_total' => (string)$order->formatPriceTxt($order->getGrandTotal())
            ];
            $billingAddress = $order->getBillingAddress();
            $mobilenumber = $billingAddress->getTelephone();

            if ($order->getCustomerId() > 0) {
                $customer = $this->customerFactory->create()->load($order->getCustomerId());
                $mobile = $customer->getMobilenumber();
                $wpterms = $customer->getWpterms();
                if ($wpterms) {
                    if ($mobile != '' && $mobile != null) {
                        $mobilenumber = $mobile;
                    }
                }

                $this->emailfilter->setVariables([
                    'order' => $order,
                    'creditmemo' => $creditmemo,
                    'customer' => $customer,
                    'mobilenumber' => $mobilenumber
                ]);
                $data['mobilenumber'] = $mobilenumber;

                $data['customer_firsname'] = $customer->getFirstname();
                $data['customer_lastname'] = $customer->getLastname();
                $data['customer_email'] = $customer->getEmail();
                $data['customer_created_at'] = (string)$customer->getCreatedAt();
            } else {
                $this->emailfilter->setVariables([
                    'order' => $order,
                    'creditmemo' => $creditmemo,
                    'mobilenumber' => $mobilenumber
                ]);
                $data['mobilenumber'] = $mobilenumber;
            }

            $data['creditmemo_id'] = (string)$creditmemo->getId();
            $data['creditmemo_increment_id'] = (string)$creditmemo->getIncrementId();
            $data['creditmemo_grand_total'] = (string)$creditmemo->getGrandTotal();
            $data['creditmemo_shipping_amount'] = (string)$creditmemo->getShippingAmount();
            $data['creditmemo_base_subtotal'] = (string)$creditmemo->getBaseSubtotal();
            $data['creditmemo_subtotal'] = (string)$creditmemo->getSubtotal();
            $data['creditmemo_tax_amount'] = (string)$creditmemo->getTaxAmount();
            $data['creditmemo_order_id'] = (string)$creditmemo->getOrderId();
            $data['creditmemo_created_at'] = (string)$creditmemo->getCreatedAt();

            $json = json_encode($data);

            if ($this->helpercreditmemo->isCreditmemoNotificationForUser($storeId)) {
                $customer = $this->customerFactory->create()->load($order->getCustomerId());
                $wpterms = $customer->getWpterms();
                $message = $this->helpercreditmemo->getCreditmemoNotificationUserTemplate($storeId);
               
                $langcode = $this->helpercreditmemo->getCreditmemoNotificationUserLangCode($storeId);
                $tempid = $this->helpercreditmemo->getCreditmemoNotificationUserTmpId($storeId);
                $params = $this->helpercreditmemo->getCreditmemoNotificationUserParams($storeId);
                $finalparams='';
                if ($params) {
                    $finalparams=$this->emailfilter->filter($params);
                }
                $finalmessage = $this->emailfilter->filter($message);
                
                /*if ($customer->getData()) {
                    if ($wpterms) {
                        $this->helperapi->callApiUrl($mobilenumber, $finalmessage, $storeId, $langcode, $tempid, $finalparams);
                    }
                } else {
                    $this->helperapi->callApiUrl($mobilenumber, $finalmessage, $storeId, $langcode, $tempid, $finalparams);
                }*/
                $csid = $this->helpercreditmemo->getCreditmemoSID($storeId=null);
                $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$csid);
            }

            if ($this->helpercreditmemo->isCreditmemoNotificationForAdmin($storeId) && $this->helpercreditmemo->getAdminNumber($storeId)) {
                $message = $this->helpercreditmemo->getCreditmemoNotificationForAdminTemplate($storeId);
                $langcode = $this->helpercreditmemo->getCreditmemoNotificationForAdminLangCode($storeId);
                $tempid = $this->helpercreditmemo->getCreditmemoNotificationForAdminTmpId($storeId);
                $params = $this->helpercreditmemo->getCreditmemoNotificationForAdminParams($storeId);
                $finalmessage = $this->emailfilter->filter($message);

                $finalparams='';
                if ($params) {
                    $finalparams=$this->emailfilter->filter($params);
                }
                //$this->helperapi->callApiUrl($this->helpercreditmemo->getAdminNumber($storeId), $finalmessage, $storeId, $langcode, $tempid, $finalparams);

                $csid = $this->helpercreditmemo->getCreditmemoAdminSID($storeId=null);
                $this->helperapi->callApiUrl($this->helpercreditmemo->getAdminNumber($storeId), $finalmessage,$storeId,$json,$csid);
            }
        }
        return $this;
    }
}
