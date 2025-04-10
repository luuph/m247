<?php
namespace Magecomp\Whatsappultimate\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;

class OrderSaveObserver implements ObserverInterface
{
    protected $helperapi;
    protected $helperorder;
    protected $emailfilter;
    protected $customerFactory;
    protected $customersession;

    public function __construct(
        \Magecomp\Whatsappultimate\Helper\Apicall $helperapi,
        \Magecomp\Whatsappultimate\Helper\Order $helperorder,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customersession
    ) {
        $this->helperapi = $helperapi;
        $this->helperorder = $helperorder;
        $this->emailfilter = $filter;
        $this->customerFactory = $customerFactory;
        $this->customersession = $customersession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId =$order->getStoreId();
        if (!$this->helperorder->isEnabled($storeId)) {
            return $this;
        }
        if ($order) {
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
                    'customer' => $customer,
                    'order_total' => $order->formatPriceTxt($order->getGrandTotal()),
                    'mobilenumber' => $mobilenumber
                ]);

                $data['mobilenumber'] = $mobilenumber;
                $data['customer_firsname'] = (string)$customer->getFirstname();
                $data['customer_lastname'] = (string)$customer->getLastname();
                $data['customer_email'] = (string)$customer->getEmail();
                $data['customer_created_at'] = (string)$customer->getCreatedAt();

            } else {
                $this->emailfilter->setVariables([
                    'order' => $order,
                    'order_total' => $order->formatPriceTxt($order->getGrandTotal()),
                    'mobilenumber' => $mobilenumber
                ]);
                $data['mobilenumber'] = $mobilenumber;
            }
            $json = json_encode($data);
            if ($this->helperorder->isOrderNotificationForUser($storeId)) {
                $customer = $this->customerFactory->create()->load($order->getCustomerId());
                $wpterms = $customer->getWpterms();
                $message = $this->helperorder->getOrderNotificationUserTemplate($storeId);
                $langcode = $this->helperorder->getOrderNotificationUserLangCode($storeId);
                $tempid = $this->helperorder->getOrderNotificationUserTmpId($storeId);
                $params = $this->helperorder->getOrderNotificationUserParams($storeId);
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
                $sid = $this->helperorder->getOrderNotificationUserSID($storeId);
                $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$sid);
            }

            if ($this->helperorder->isOrderNotificationForAdmin($storeId) && $this->helperorder->getAdminNumber($storeId)) {
                $message = $this->helperorder->getOrderNotificationForAdminTemplate($storeId);
                $langcode = $this->helperorder->getOrderNotificationForAdminLangCode($storeId);
                $tempid = $this->helperorder->getOrderNotificationForAdminTmpId($storeId);
                $params = $this->helperorder->getOrderNotificationForAdminParams($storeId);
                $finalparams='';
                if ($params) {
                    $finalparams=$this->emailfilter->filter($params);
                }
                $finalmessage = $this->emailfilter->filter($message);
               // $this->helperapi->callApiUrl($this->helperorder->getAdminNumber($storeId), $finalmessage, $storeId, $langcode, $tempid, $finalparams);

                $sid = $this->helperorder->getOrderNotificationForAdminSID($storeId);
                $this->helperapi->callApiUrl($this->helperorder->getAdminNumber($storeId), $finalmessage,$storeId,$json,$sid);
            }
        }
        return $this;
    }
}
