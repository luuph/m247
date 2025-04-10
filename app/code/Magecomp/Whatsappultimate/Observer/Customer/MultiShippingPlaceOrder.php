<?php

namespace Magecomp\Whatsappultimate\Observer\Customer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class MultiShippingPlaceOrder implements ObserverInterface
{
    protected $orderFactory;
    protected $helperorder;
    protected $customerFactory;
    protected $emailfilter;
    protected $helperapi;
    protected $checkoutsession;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magecomp\Whatsappultimate\Helper\Order $helperorder,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Email\Model\Template\Filter $emailfilter,
        \Magecomp\Whatsappultimate\Helper\Apicall $helperapi,
        \Magento\Checkout\Model\Session $checkoutsession
    ) {
        $this->orderFactory = $orderFactory;
        $this->helperorder = $helperorder;
        $this->customerFactory = $customerFactory;
        $this->emailfilter = $emailfilter;
        $this->helperapi = $helperapi;
        $this->checkoutsession = $checkoutsession;
    }

    public function execute(Observer $observer)
    {
        // Get the order IDs from the event
        $orderIds = $observer->getEvent()->getOrderIds();

        if (is_array($orderIds)) {
            foreach ($orderIds as $orderId) {
                // Load the order by ID
                $order = $this->orderFactory->create()->load($orderId);

                if (!$this->helperorder->isEnabled($order->getStoreId())) {
                    continue;
                }

                if ($order) {
                    $blockednumber = array();
                    $billingAddress = $order->getBillingAddress();
                    $mobilenumber = $billingAddress->getTelephone();
                    $otpin = false;
                    $customer = $this->customerFactory->create()->load($order->getCustomerId());
                    $mobile = $customer->getMobilenumber();
                    $otpin = $customer->getOtpin();
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

                    if ($order->getCustomerId() > 0) {

                        if ($mobile != '' && $mobile != null) {
                            $mobilenumber = $mobile;
                        }
                        if ($otpin == '1') {
                            $mobilenumber = $mobile;
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

                    if ($this->helperorder->isOrderNotificationForUser($order->getStoreId())) {
                        $wpterms = $customer->getWpterms();
                        $message = $this->helperorder->getOrderNotificationUserTemplate($order->getStoreId());
                        $tempid = $this->helperorder->getOrderNotificationUserTmpId($order->getStoreId());
                        $langcode = $this->helperorder->getOrderNotificationUserLangCode($order->getStoreId());
                        $finalparams = $this->helperorder->getOrderNotificationUserParams($order->getStoreId());

                        $finalmessage = $this->emailfilter->filter($message);
                       /* if ($customer->getData()) {
                            if ($wpterms) {
                                $this->helperapi->callApiUrl($mobilenumber, $finalmessage, $order->getStoreId(), $langcode, $tempid, $finalparams);
                            }
                        }
                        else{
                            $this->helperapi->callApiUrl($mobilenumber, $finalmessage, $order->getStoreId(), $langcode, $tempid, $finalparams);
                        }*/
                        $sid = $this->helperorder->getOrderNotificationUserSID($order->getStoreId());
                        $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$order->getStoreId(),$json,$sid);
                    }

                    if ($this->helperorder->isOrderNotificationForAdmin($order->getStoreId()) && $this->helperorder->getAdminNumber($order->getStoreId())) {
                        $message = $this->helperorder->getOrderNotificationForAdminTemplate($order->getStoreId());
                        $tempid = $this->helperorder->getOrderNotificationForAdminTmpId($order->getStoreId());
                        $langcode = $this->helperorder->getOrderNotificationForAdminLangCode($order->getStoreId());
                        $finalparams = $this->helperorder->getOrderNotificationForAdminParams($order->getStoreId());
                        $finalmessage = $this->emailfilter->filter($message);
                        
                      //  $this->helperapi->callApiUrl($this->helperorder->getAdminNumber($order->getStoreId()), $finalmessage, $order->getStoreId(), $langcode, $tempid, $finalparams);

                        $sid = $this->helperorder->getOrderNotificationForAdminSID($order->getStoreId());
                        $this->helperapi->callApiUrl($this->helperorder->getAdminNumber($order->getStoreId()), $finalmessage,$order->getStoreId(),$json,$sid);
                    }

                    $this->checkoutsession->setGuestOrderConfirm('0');
            }
        }
        }

        return $this;
    }
}