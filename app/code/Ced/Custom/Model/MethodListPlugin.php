<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Custom
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Custom\Model;
/*
 * used to save data in Quote To OrderItem table
 * */
class MethodListPlugin
{

    /**
     * @var \Magento\Payment\Helper\Data
     * @deprecated
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Payment\Model\Checks\SpecificationFactory
     */
    protected $methodSpecificationFactory;

    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Payment\Model\Method\InstanceFactory
     */
    private $paymentMethodInstanceFactory;

    protected $_objectManager;
    protected $resultPageFactory;
    protected $_helper;
    protected $_checkoutSession;

    protected $_scopeConfig;

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param Checks\SpecificationFactory $specificationFactory
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $specificationFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Cart $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->methodSpecificationFactory = $specificationFactory;
        $this->_objectManager = $objectManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Payment\Model\MethodInterface[]
     * @api
     */
    public function aftergetAvailableMethods($subject, $result)
    {
        $quote = $this->_checkoutSession->getQuote();
        //var_dump($quote->getShippingAddress()->getShippingMethod());die;
        if ($quote && $quote->getShippingAddress()->getCountryId() && $this->_scopeConfig->getValue('carriers/webkularamex/cod_sallowspecific', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $dest_country = $quote->getShippingAddress()->getCountryId();
            $value = $this->_scopeConfig->getValue('carriers/webkularamex/cod_specificcountry', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $allowed_country = explode(',', $value);
            if(!in_array($dest_country, $allowed_country)){
                foreach($result as $key => $payment){
                    //echo $payment->getCode();
                    if($payment->getCode() == 'webkularamex'){
                        unset($result[$key]);
                    }

                }
                //return $result;
            }else{
                //return $result;
            }
        }if($quote->getShippingAddress()->getShippingMethod()){
            if($quote->getShippingAddress()->getShippingMethod() != 'localshipping_localshipping'){
                foreach($result as $key => $payment){
                    //echo $payment->getCode();
                    if($payment->getCode() == 'msp_cashondelivery'){
                        unset($result[$key]);
                    }

                }

            }

        }/*else{
            return $result;
        }*/
        return $result;
    }
}
