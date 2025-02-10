<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FME\RestrictPaymentMethod\Model\Config\Source;

use \Magento\Framework\App\Config\ScopeConfigInterface;

class Allmethods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface
     */
    private $appConfigScopeConfigInterface;
    /**
     * Order Payment
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Collection
     */
    private $orderPayment;
    /**
     * Payment Helper Data
     *
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentHelper;
    /**
     * Payment Model Config
     *
     * @var \Magento\Payment\Model\Config
     */
    private $paymentConfig;
    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $orderPayment
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Payment\Model\Config $paymentConfig
     */
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Config $paymentConfig
    ) {
        $this->appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->paymentHelper = $paymentHelper;
        $this->paymentConfig = $paymentConfig;
    }
    /**
     * Get all payment methods
     *
     * @return array
     */
    public function getAllPaymentMethods()
    {
        return $this->paymentHelper->getPaymentMethods();
    }
    /**
     * Get key-value pair of all payment methods
     * key = method code & value = method name
     *
     * @return array
     */
    public function getAllPaymentMethodsList()
    {
        return $this->paymentHelper->getPaymentMethodList();
    }
    /**
     * Get active/enabled payment methods
     *
     * @return array
     */
    public function getActivePaymentMethods()
    {
        return $this->paymentConfig->getActiveMethods();
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $payments = $this->getActivePaymentMethods();
        $methods = [];
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->appConfigScopeConfigInterface
                ->getValue('payment/'.$paymentCode.'/title');
            $methods[$paymentCode] = [
                'label' => $paymentTitle,
                'value' => $paymentCode
            ];
        }
        return $methods;
    }
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        foreach ($this->getActivePaymentMethods() as $method) {
            $paymentMethods[] =  $method->getAdditionalInformation()['method_title'];
        }
        return $paymentMethods;
    }//end toArray()
}//end class
