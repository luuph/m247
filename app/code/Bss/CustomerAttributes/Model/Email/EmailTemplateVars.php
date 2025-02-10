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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2024-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Model\Email;

class EmailTemplateVars
{
    /**
     * @var \Bss\CustomerAttributes\Helper\GetHtmltoEmail
     */
    protected $helper;

    /**
     * EmailTemplateVars constructor.
     *
     * @param \Bss\CustomerAttributes\Helper\GetHtmltoEmail $helper
     */
    public function __construct(
        \Bss\CustomerAttributes\Helper\GetHtmltoEmail $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Set email template var
     *
     * @param $transport
     * @param $order
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setEmailTemplateVars($transport, $order)
    {
        $customerBillingAddress = $order->getBillingAddress()->getCustomerAddressAttribute();
        if ($order->getIsVirtual()) {
            $customerShippingAddress = null;
        } else {
            $customerShippingAddress = $order->getShippingAddress()->getCustomerAddressAttribute();
        }

        if (!$order->getCustomerId()) {
            $this->setEmailTemplateVariableGuest($order, $transport, $customerShippingAddress, $customerBillingAddress);
        } else {
            $this->setEmailTemplateVariableCustomer($order, $transport, $customerShippingAddress, $customerBillingAddress);
        }
    }

    /**
     * Set email template variable guest
     *
     * @param $order
     * @param $transport
     * @param $customerShippingAddress
     * @param $customerBillingAddress
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setEmailTemplateVariableGuest($order, $transport, $customerShippingAddress, $customerBillingAddress)
    {
        $transport['bss_customer_attributes'] = $this->helper->getVariableEmailHtmlForGuest(
            $order->getData('customer_attribute'),
            $order->getStoreId()
        );
        $transport['bss_billing_address_attributes'] = $this->helper->getAddressVariableGuestEmailHtml(
            $customerBillingAddress
        );
        $transport['bss_shipping_address_attributes'] = $this->helper->getAddressVariableGuestEmailHtml(
            $customerShippingAddress
        );
    }

    /**
     * Set email template variable customer
     *
     * @param $order
     * @param $transport
     * @param $customerShippingAddress
     * @param $customerBillingAddress
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setEmailTemplateVariableCustomer($order, $transport, $customerShippingAddress, $customerBillingAddress)
    {
        $transport['bss_customer_attributes'] = $this->helper
            ->getVariableEmailHtml($order->getCustomerId(), $order->getStoreId());
        $transport['bss_billing_address_attributes'] = $this->helper->getAddressVariableOrderEmailHtml(
            $customerBillingAddress,
            $order->getCustomerId()
        );
        $transport['bss_shipping_address_attributes'] = $this->helper->getAddressVariableOrderEmailHtml(
            $customerShippingAddress,
            $order->getCustomerId()
        );
    }
}
