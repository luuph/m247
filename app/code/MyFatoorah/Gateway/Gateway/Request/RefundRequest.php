<?php

namespace MyFatoorah\Gateway\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class RefundRequest implements BuilderInterface
{
    //---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Builds ENV request
     * From: https://github.com/magento/magento2/blob/2.1.3/app/code/Magento/Payment/Model/Method/Adapter.php
     * The $buildSubject contains:
     * 'amount'
     * 'payment' => $this->getInfoInstance() which contains PaymentDataObjectorder and PaymentDataObjectpayment,
     * use getOrder and getPayment
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [];
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------
}
