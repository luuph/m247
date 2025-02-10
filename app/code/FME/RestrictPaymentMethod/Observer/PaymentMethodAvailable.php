<?php
/**
 * FME Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the fmeextensions.com license that is
 * available through the world-wide-web at this URL:
 * https://www.fmeextensions.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @copyright Copyright (c) 2019 FME (http://fmeextensions.com/)
 * @license   https://fmeextensions.com/LICENSE.txt
 */

namespace FME\RestrictPaymentMethod\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class PaymentMethodAvailable
 *
 * @package FME\RestrictPaymentMethod\Observer
 */
class PaymentMethodAvailable implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session::Quote
     */
    private $quote;
    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $helper;
    protected $_messageManager;
    protected $quotemodel;
    protected $model;
    protected $couponmodel;
    protected $_storeManager;
    protected $customerSession;
    protected $collection;
    protected $factoryMethod;
    protected $_checkoutSession;
    protected $_carrierFactory;

    /**
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Payment\Helper\Data    $helper
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory,
        \FME\RestrictPaymentMethod\Model\ResourceModel\PaymentMethod\CollectionFactory $collection,
        \FME\RestrictPaymentMethod\Model\PaymentMethodFactory $factoryMethod,
        \FME\RestrictPaymentMethod\Model\PaymentMethod $model,
        \FME\RestrictPaymentMethod\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\SalesRule\Model\Coupon $couponmodel,
        \Magento\Quote\Model\Quote $quotemodel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_messageManager = $messageManager;
        $this->quotemodel = $quotemodel;
        $this->model = $model;
        $this->couponmodel = $couponmodel;
        $this->_storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->collection =$collection;
        $this->factoryMethod =$factoryMethod;
        $this->_checkoutSession = $_checkoutSession;
        $this->_carrierFactory = $carrierFactory;
        $this->quote  = $session->getQuote();
        $this->helper = $helper;
    }//end __construct()

    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isEnabledInFrontend()) {
            return;
        }

        /** @var \Magento\Payment\Model\MethodInterface $methodInstance */
        $methodInstance = $observer->getEvent()->getMethodInstance();
        
        $paymentCode = $methodInstance->getCode();
        if ($paymentCode === 'payflow_express_bml') { // disable code for strip integration module because he is not loading in get code;
            $paymentCode = 'stripe_payments';
        }
        //echo $paymentCode.'<br>';

        /** @var \Magento\Framework\DataObject $checkResult */
        $checkResult = $observer->getEvent()->getData('result');

        // Get quote from session
        $quote = $this->_checkoutSession->getQuote();
        if (!$quote) {
            return;
        }

        // Get quote ID
        $quoteId = $quote->getId();

        // Get Address (Billing or Shipping)
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();


        // Validate payment method
        $allowed = $this->validatePaymentMethod($paymentCode, $address, $quoteId);
        
        if ($allowed) {
            $checkResult->setData('is_available', false);
        }
    }
    /*public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if (!$this->helper->isEnabledInFrontend()) {
            return ;
        }

        $quoteItem = $this->_checkoutSession;
        $quoteId = $observer->getEvent()->getQuoteId();
        $model=$this->quotemodel->load($observer->getEvent()->getQuoteId());
          if ($this->quote->isVirtual()) {
            $address = $this->quote->getBillingAddress();
          } else {
            $address = $this->quote->getShippingAddress();
          }
        $paymentCode=$observer->getEvent()->getMethodInstance()->getCode();
        $allowed=$this->validatePaymentMethod($paymentCode, $address,$quoteId);
        if ($allowed) {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', 0);
                // this is disabling the payment method at checkout page
        }
    }*/

    public function validatePaymentMethod($paymentMethod, $address, $quoteId)
    {
        $customerGroupId=0;
        if ($this->customerSession->isLoggedIn())
            $customerGroupId=$this->customerSession->getCustomer()->getGroupId();
        $model=$this->quotemodel->load($quoteId);
        $collection = $this->collection->create();
        $factoryMethod = $this->factoryMethod->create();
        $collection = $factoryMethod->getCollection();
        $collection->addStoreFilter($this->_storeManager->getStore()->getId());
        $collection->addPaymentMethodsFilter($paymentMethod);
        $collection->addPriorityFilter();
        $collection->addStatusFilter();
        $collection->addCustomerGroupFilter($customerGroupId);
        $collection->getSelect($collection);
        if ($collection->count() < 1) {
            return false;
        }
        foreach ($collection as $value) {
            $data = $this->model->load($value->getRuleId());
            $paymentdata=$data->getData();
            $operation= $data->getOperation();
            $ncartrules=$this->validateNotAppliedSalesRuleIds($paymentdata, $model);
            if($ncartrules === false) {
                    return false;
            } else {
                if($ncartrules != "empty") {
                    if($operation === "1") {
                        return true;
                    }
                }
            }
            $ncoponcodes=$this->validateNotApplyCouponCode($paymentdata, $model);
            if($ncoponcodes === false) {
                    return false;
            } else {
                if($ncoponcodes != "empty") {
                    if($operation === "1") {
                        return true;
                    }
                }
            }
            $conResult = "";
            $conditions=$data->getConditions()->validate($address);
            if($conditions) {
                if($operation === "1") {
                    return true;
                } else {
                    $conResult = $this->returnResult($paymentdata['operation']);
                }
            } else {

                if($operation === "0") {
                    continue;
                }
            }
            $customers=$this->validateCustomer($paymentdata);
            if($customers) {
                if($operation === "1") {
                    return true;
                }
            } else {
                if($operation === "0") {
                    continue;
                }
            }
            $billingAddressInfo = $this->quote->getBillingAddress();
            $billingAddressData = $billingAddressInfo->getData();
            if((isset($billingAddressData['postcode']) && !empty($paymentdata['postcode']))) {
                $postCode=$this->validatePostCode($paymentdata,$billingAddressData);
            } else {
                $postCode= $this->returnResult($paymentdata['operation']);
            }
            if($postCode) {
                if($operation === "1") {
                    return true;
                }
            } else {
                if($operation === "0") {
                    continue;
                }
            }
            if($paymentdata['grandtotal']!='' ||$paymentdata['grandtotal']!=NULL) {
                $grandTotal=$this->validateGrandTotal($paymentdata);
            } else {
                $grandTotal=$this->returnResult($paymentdata['operation']);  
            }
            if($grandTotal) {
                if($operation === "1") {
                    return true;
                }
            } else {
                if($operation === "0") {
                    continue;
                }
            }
            if(isset($paymentdata['restrictoptions']) && $paymentdata['restrictoptions']==0) {
                $place=$this->validateCountries($paymentdata,$billingAddressData);
            } else if(isset($paymentdata['restrictoptions']) && $paymentdata['restrictoptions']==1 && isset($billingAddressData['region_id'])) {
                $place=$this->validateRegion($paymentdata,$billingAddressData);
            } else {
                $place= $this->returnResult($paymentdata['operation']);
            }
            if($place) {
                if($operation === "1") {
                    return true;
                }
            } else {
                if($operation === "0") {
                    continue;
                }
            }
            $cartrules=$this->validateAppliedSalesRuleIds($paymentdata, $model);
            if($cartrules) {
                if($operation === "1") {
                    return true;
                }
            } else {
                if($operation === "0") {
                    continue;
                }
            }
            $copponcodes=$this->validateCouponCode($paymentdata, $model);
            if($copponcodes) {
                if($operation === "1") {
                    return true;
                }
            } else {
                if($operation === "0") {
                    continue;
                }
            }
            $dayTimings=$this->validateDayTiming($paymentdata);
            if($dayTimings) {
                if($operation === "1") {
                    return true;
                }
            } else {
                if($operation === "0") {
                    continue;
                }
            }
            if($operation == "0") {
                if($ncartrules == true && $ncoponcodes == true && $conditions == true && $customers == true && $postCode == true && $grandTotal == true && $place == true && $cartrules == true && $copponcodes == true && $dayTimings == true) {
                    return true;
                }
            }
            if($operation == "1") {
                if($ncartrules != "empty" && $ncoponcodes) {
                    if($ncartrules == true ||$ncoponcodes == true || $conditions == true || $customers == true || $postCode == true || $grandTotal == true || $place == true || $cartrules == true || $copponcodes == true || $dayTimings == true) {
                        return true;
                    }
                }
            }
            return false;
        }
        return false;
    }
    public function validateCountries($paymentdata, $billingData)
    {
        $coundryId= $billingData['country_id'];
        if ($coundryId!=='' || $coundryId!==null) {
                if (in_array($coundryId, $paymentdata['country'])) {
                    return true;
                } else {
                    return false;
                }
        } else {
            return false;
        }
        return $this->returnResult($paymentdata['operation']);
    }
    public function validatePostCode($paymentdata, $billingData)
    {
        $postCode= $billingData['postcode'];
        if ($postCode!=='' || $postCode!==null) {
                $assignedPostalCode=explode(',', $paymentdata['postcode']);
                if (in_array($postCode, $assignedPostalCode)) {
                    return true;
                } else {
                    return false;
                }
        } else {
            return false;
        }
        return $this->returnResult($paymentdata['operation']);
    }
    public function validateGrandTotal($paymentdata)
    {
        $grandTotal= $this->quote->getGrandTotal();
        $assignGrandTotal= $paymentdata['grandtotal'];
        switch ($paymentdata['total_operation']) {
            case "=":  return $grandTotal == $assignGrandTotal;
            case "!=": return $grandTotal != $assignGrandTotal;
            case ">=": return $grandTotal >= $assignGrandTotal;
            case "<=": return $grandTotal <= $assignGrandTotal;
            case ">":  return $grandTotal >  $assignGrandTotal;
            case "<":  return $grandTotal <  $assignGrandTotal;
            default:
            return $this->returnResult($paymentdata['operation']);
        }
    }
    public function validateRegion($paymentdata, $billingData)
    {
        $regionId= $billingData['region_id'];
        if ($regionId!=='' || $regionId!==null) {
                if (in_array($regionId, $paymentdata['region_id'])) {
                    return true;
                } else {
                    return false;
                }
            } else  {
                return false;
            }
        return $this->returnResult($paymentdata['operation']);
    }
    public function validateNotApplyCouponCode($paymentdata, $subject) 
    {
        if (!empty($paymentdata['noapply_coupon_id']) && $paymentdata['noapply_coupon_id']['0']!='') {
            $noApplyCouponCode=$this->quote->getCouponCode();
            if($noApplyCouponCode) {
                $noApplyCouponData=$this->couponmodel->loadByCode($noApplyCouponCode);
                $couponId=$noApplyCouponData->getCouponId();
                if (in_array($couponId, $paymentdata['noapply_coupon_id'])) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        } else {
            return "empty";
        }
    }
    public function validateCouponCode($paymentdata, $subject)
    {
        if (!empty($paymentdata['apply_coupon_id']) && $paymentdata['apply_coupon_id']['0']!='') {
            $ApplyCouponCode=$this->quote->getCouponCode();
            if($ApplyCouponCode) {
                $ApplyCouponData=$this->couponmodel->loadByCode($ApplyCouponCode);
                $couponId=$ApplyCouponData->getCouponId();
                if (in_array($couponId, $paymentdata['apply_coupon_id'])) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return $this->returnResult($paymentdata['operation']);
        }
    }
    public function validateAppliedSalesRuleIds($paymentdata, $subject)
    {

        if(!empty($paymentdata['apply_catalog_rule'])) {
            $ruleIds = $this->quote->getFmeAppliedRuleIds();
            if(!empty($ruleIds)) {
                $ApplySalesRuleIds = explode(',', $ruleIds);
                $result=array_intersect($ApplySalesRuleIds, $paymentdata['apply_catalog_rule']);
                if (!empty($result)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return $this->returnResult($paymentdata['operation']);
        }
    }
    public function validateNotAppliedSalesRuleIds($paymentdata, $subject)
    {

        if(!empty($paymentdata['noapply_catalog_rule'])) {
            $ruleIds = $this->quote->getFmeAppliedRuleIds();
            if(!empty($ruleIds)) {
                $noApplySalesRuleIds = explode(',', $ruleIds);
                $result=array_intersect($noApplySalesRuleIds, $paymentdata['noapply_catalog_rule']);
                if (!empty($result)) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        } else {
            return "empty";
        }
    }
    public function validateCustomer($paymentdata)
    {
        $paymentdata['customers']=(array)json_decode($paymentdata['customers']);
        if (!empty($paymentdata['customers'])) {
            if ($this->customerSession->isLoggedIn()) {
                $customerId=$this->customerSession->getCustomer()->getId();
                if (in_array($customerId, $paymentdata['customers'])) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return $this->returnResult($paymentdata['operation']);
        }
    }
    protected function validateDayTiming($data)
    {
        $time = date("Hi");
        $day=date("D");
        $sunday = $monday =$tuesday = $wednesday = $thursday = $friday = $saturday = false;
        $data=$this->prepareTime($data);
        if(isset($data['timing'])) {
            foreach ($data['timing'] as $value) {
                if (!isset($value['day_id'])) {
                    return $this->returnResult($data['operation']);
                }
                if ($value['day_id']=='0' && $day==='Sun') {
                    if ($time>=$value['open_at'] && $time<=$value['close_at']) {
                        $sunday=true;
                    }
                }
                if ($value['day_id']=='1' && $day==='Mon') {
                    if ($time>=$value['open_at'] && $time<=$value['close_at']) {
                        $monday=true;
                    }
                }
                if ($value['day_id']=='2' && $day==='Tue') {
                    if ($time>=$value['open_at'] && $time<=$value['close_at']) {
                        $tuesday =true;
                    }
                }
                if ($value['day_id']=='3' && $day==='Wed') {
                    if ($time>=$value['open_at'] && $time<=$value['close_at']) {
                        $wednesday = true;
                    }
                }
                if ($value['day_id']=='4' && $day==='Thu') {
                    if ($time >= $value['open_at'] && $time <= $value['close_at']) {
                        $thursday = true;
                    }
                }
                if ($value['day_id']=='5' && $day==='Fri') {
                    if ($time >= $value['open_at'] && $time <= $value['close_at']) {
                        $friday = true;
                    }
                }
                if ($value['day_id']=='6' && $day==='Sat') {
                    if ($time >= $value['open_at'] && $time <= $value['close_at']) {
                        $saturday = true;
                    }
                }
            }
            if($sunday || $monday ||$tuesday || $wednesday || $thursday || $friday || $saturday){
                return true;
            } else {
                return false;
            }
        } else {
            return $this->returnResult($data['operation']);
        }
    }
    protected function prepareTime($data)
    {
        $day=array();
        if (isset($data['assign_timing'])) :
            $dayList=$data['assign_timing'];
            for ($i=0; $i<sizeof($dayList); $i++) {
                $day[$i]['day_id']=$dayList[$i]['day'];
                $day[$i]['open_at']=$dayList[$i]['hopen'].$dayList[$i]['mopen'];
                $day[$i]['close_at']=$dayList[$i]['hclose'].$dayList[$i]['mclose'];
            }
            unset($data['assign_timing']);
            $data['timing']=$day;
        endif;
        return $data;
    }
    public function returnResult($operator)
    {
        if ($operator==='0')
            return true;
        if($operator==='1')
            return false;
    }
}