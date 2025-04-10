<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphql
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Checkout;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * PlaceOrder resolver
 */
class PlaceOrder extends AbstractCheckout implements ResolverInterface
{
    /**
     * Method variable
     *
     * @var string
     */
    protected $method;

    /**
     * CcCid variable
     *
     * @var string
     */
    protected $cc_cid;

    /**
     * CcType variable
     *
     * @var string
     */
    protected $cc_type;

    /**
     * CcNumber variable
     *
     * @var string
     */
    protected $cc_number;

    /**
     * CcExpYear variable
     *
     * @var string
     */
    protected $cc_exp_year;

    /**
     * BillingData variable
     *
     * @var array
     */
    protected $billingData;

    /**
     * CcExpMonth variable
     *
     * @var string
     */
    protected $cc_exp_month;

    /**
     * CheckoutMethod variable
     *
     * @var string
     */
    protected $checkoutMethod;

    /**
     * PoNumber variable
     *
     * @var string
     */
    protected $poNumber;

    /**
     * PurchasePoint variable
     *
     * @var string
     */
    protected $purchasePoint;

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->wholeData = $args;
        $invoiceId = $this->wholeData['invoiceId'] ?? null;
        $gatewayName = $this->wholeData['gatewayName'] ?? null;
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\App\ResourceConnection::class);
        $connection = $resource->getConnection();
        $tableName = $connection->getTableName('myfatoorah_invoice');

        if ($invoiceId) {
            // Insert the invoice_id and gateway name without order_id
            $data = [
                'invoice_id' => $invoiceId
            ];
            if ($gatewayName) {
                $data['gateway_name'] = $gatewayName;
            }
            $connection->insertOnDuplicate($tableName, $data, array_keys($data));
        }
        

        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $quote = $this->helper->getCustomerQuote($this->customerId);
            }
            if ($this->quoteId != 0) {
                $quote = $this->helper->getQuoteById($this->quoteId)->setStoreId($this->storeId);
            }
            if ($quote->getItemsQty()*1 == 0) {
                $this->returnArray["message"] = __("Sorry Something went wrong !!");
                return $this->returnArray;
            } else {
                $this->returnArray["cartCount"] = (int) $quote->getItemsQty()*1;
            }
            $selectedShipping = $quote->getShippingAddress()->getShippingMethod();
            // Set Billing Address in quote /////////////////////////////////////////
            $this->setBillingDataInQuote($quote);
            // set payment information in quote /////////////////////////////////////
            $this->savePaymentInfo($quote);
            /**
             * set shipping method again to the new address
             */
            $quote->getShippingAddress()->setShippingMethod($selectedShipping);
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->collectTotals()->save();

            // validate minimum amount check ////////////////////////////////////////
            $isCheckoutAllowed = $quote->validateMinimumAmount();
            if (!$isCheckoutAllowed) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($this->helper->getConfigData("sales/minimum_order/description"))
                );
            }
            if ($quote->getCheckoutMethod() == "guest" &&
                !$this->checkoutHelper->isAllowedGuestCheckout($quote, $quote->getStoreId())
            ) {
                $this->returnArray["message"] = __("Guest Checkout is not Enabled");
                return $this->returnArray;
            }
            $isNewCustomer = false;
            switch ($quote->getCheckoutMethod()) {
                case "guest":
                    $quote = $this->prepareGuestQuote($quote);
                    break;
                case "register":
                    $quote = $this->prepareNewCustomerQuote($quote);
                    $isNewCustomer = true;
                    break;
                default:
                    $quote = $this->prepareCustomerQuote($quote, $this->customerId);
                    break;
            }
            $remoteAddress = $this->remoteAddress->getRemoteAddress();
            if ($remoteAddress) {
                $quote->setRemoteIp($remoteAddress);
                $xForwardIp = $this->getRequest()->getServer('HTTP_X_FORWARDED_FOR');
                $quote->setXForwardedFor($xForwardIp);
            }
            $quote->collectTotals()->save();
            $order = $this->quoteManagement->submit($quote);
            
            if ($invoiceId && $order) {
                $updateData = [
                    'order_id' => $order->getIncrementId()
                ];
                if ($gatewayName) {
                    $updateData['gateway_name'] = $gatewayName;
                }
                $connection->update(
                    $tableName,
                    $updateData,
                    ['invoice_id = ?' => $invoiceId]
                );
            }
            
            
            if ($order->getCustomerIsGuest()) {
                $tokenCollection = $this->deviceToken->getCollection()->addFieldToFilter("token", $this->token);
                foreach ($tokenCollection as $eachToken) {
                    $eachToken->setEmail($order->getCustomerEmail());
                    $eachToken->setId($eachToken->getId());
                    $eachToken->save();
                }
            }
            if ($isNewCustomer) {
                $result = $this->involveNewCustomer($quote);
                if (!$result["status"]) {
                    $this->returnArray["message"] = $result["message"];
                    return $this->returnArray;
                }
            }
            if ($order) {
                $this->eventManager->dispatch(
                    "checkout_type_onepage_save_order_after",
                    ["order"=>$order, "quote"=>$quote]
                );
                $this->saveMobikulOrder($order);
                try {
                    $this->orderEmailSender->send($order);
                } catch (\Exception $e) {
                    $this->returnArray["message"] = $e->getMessage();
                    return $this->returnArray;
                }
            }
            $this->eventManager->dispatch("checkout_submit_all_after", ["order"=>$order, "quote"=>$quote]);
            // checknig wheather user is new user or returned customer //////////////
            $userEmail = $order->getCustomerEmail();
            $websiteId = $this->store->load($order->getStoreId())->getWebsiteId();
            $customerModel = $this->customerFactory->create();
            $customer = $customerModel->setWebsiteId($websiteId)->loadByEmail($userEmail);
            if (!$customer->getId()) {
                $this->returnArray["showCreateAccountLink"] = true;
            }
            $this->returnArray["email"] = $userEmail;
            if ($this->salesHelper->canReorder($order->getEntityId())) {
                $this->returnArray["canReorder"] = true;
            }
            $quote->collectTotals()->setIsActive(0)->setReservedOrderId(null)->save();
            $this->getCustomerDetails($order);
            $this->savePurchasePointDetail($order);
            $this->returnArray["orderId"] = $order->getId();
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Thank you for your purchase!");
            $this->returnArray["incrementId"] = $order->getIncrementId();
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * Function to prepae quote in case of guest checkout
     *
     * @param Magento\Quote\Model\Quote $quote quote
     *
     * @return Magento\Quote\Model\Quote
     */
    public function prepareGuestQuote($quote)
    {
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
        return $quote;
    }

    /**
     * Function to add OrderDetails to the return Array for create account feature
     *
     * @param \Magento\Sale\Model\Order $order order
     *
     * @return void
     */
    public function getCustomerDetails($order)
    {
        $this->returnArray['customerDetails']['guestCustomer'] = $order->getCustomerIsGuest();
        $this->returnArray['customerDetails']['groupId'] = $order->getCustomerGroupId();
        $this->returnArray['customerDetails']['firstname'] = $order->getCustomerFirstname();
        $this->returnArray['customerDetails']['email'] = $order->getCustomerEmail();
        $this->returnArray['customerDetails']['middlename'] = $order->getCustomerMiddlename();
        $this->returnArray['customerDetails']['lastname'] = $order->getCustomerLastname();
    }

    /**
     * PrepareNewCustomerQuote function
     *
     * @param mixed $quote
     * @return void
     */
    public function prepareNewCustomerQuote($quote)
    {
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
        $customer = $quote->getCustomer();
        $customerBillingData = $billing->exportCustomerAddress();
        $dataArray = $this->objectCopyService->getDataFromFieldset("checkout_onepage_quote", "to_customer", $quote);
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $dataArray,
            "\Magento\Customer\Api\Data\CustomerInterface::class"
        );
        $quote->setCustomer($customer)->setCustomerId(true);
        $customerBillingData->setIsDefaultBilling(true);
        if ($shipping) {
            if (!$shipping->getSameAsBilling()) {
                $customerShippingData = $shipping->exportCustomerAddress();
                $customerShippingData->setIsDefaultShipping(true);
                $shipping->setCustomerAddressData($customerShippingData);
                // Add shipping address to quote since customer Data Object does not hold address information ////
                $quote->addCustomerAddress($customerShippingData);
            } else {
                $shipping->setCustomerAddressData($customerBillingData);
                $customerBillingData->setIsDefaultShipping(true);
            }
        } else {
            $customerBillingData->setIsDefaultShipping(true);
        }
        $billing->setCustomerAddressData($customerBillingData);
        $quote->addCustomerAddress($customerBillingData);
        return $quote;
    }

    /**
     * PrepareCustomerQuote function
     *
     * @param mixed $quote
     * @param mixed $customerId
     * @return void
     */
    public function prepareCustomerQuote($quote, $customerId)
    {
        $quoteBilling = $quote->getBillingAddress();
        $quoteShipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
        $customer = $this->customerRepository->getById($customerId);
        $hasDefaultBilling = (bool)$customer->getDefaultBilling();
        $hasDefaultShipping = (bool)$customer->getDefaultShipping();
        if ($quoteShipping
            && !$quoteShipping->getSameAsBilling()
            && (!$quoteShipping->getCustomerId()
            || $quoteShipping->getSaveInAddressBook())
        ) {
            $shippingAddress = $quoteShipping->exportCustomerAddress();
            if (!$hasDefaultShipping) {
                // Make provided address as default shipping address ////////////////
                $shippingAddress->setIsDefaultShipping(true);
                $hasDefaultShipping = true;
            }
            $quote->addCustomerAddress($shippingAddress);
            $quoteShipping->setCustomerAddressData($shippingAddress);
        }
        if (!$quoteBilling->getCustomerId() || $quoteBilling->getSaveInAddressBook()) {
            $billingAddress = $quoteBilling->exportCustomerAddress();
            if (!$hasDefaultBilling) {
                // Make provided address as default shipping address ////////////////
                if (!$hasDefaultShipping) {
                    // Make provided address as default shipping address ////////////
                    $billingAddress->setIsDefaultShipping(true);
                }
                $billingAddress->setIsDefaultBilling(true);
            }
            $quote->addCustomerAddress($billingAddress);
            $quoteBilling->setCustomerAddressData($billingAddress);
        }
        return $quote;
    }

    /**
     * InvolveNewCustomer function
     *
     * @param mixed $quote
     * @return void
     */
    public function involveNewCustomer($quote)
    {
        $customer = $quote->getCustomer();
        $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
        if ($confirmationStatus === \Magento\Customer\Model\AccountManagement::ACCOUNT_CONFIRMATION_REQUIRED) {
            return ["status"=>false, "message"=>__(
                "You must confirm your account. Please check your email for the confirmation link."
            )];
        } else {
            return ["status"=>true, "message"=>""];
        }
    }

    /**
     * Function to verify request
     *
     * @return void|json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->token = $this->wholeData["token"] ?? "";
            $this->method = $this->wholeData["paymentMethod"] ?? "";
            $this->cc_cid = $this->wholeData["cc_cid"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->cc_type = $this->wholeData["cc_type"] ?? "";
            $this->cc_number = $this->wholeData["cc_number"] ?? "";
            $this->cc_exp_year = $this->wholeData["cc_exp_year"] ?? "";
            $this->billingData = $this->wholeData["billingData"] ?? [];
            $this->cc_exp_month = $this->wholeData["cc_exp_month"] ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->checkoutMethod = $this->wholeData["checkoutMethod"] ?? "guest";
            $this->poNumber = $this->wholeData["poNumber"] ?? '';
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            $this->purchasePoint = $this->wholeData["purchasePoint"] ?? "web";
            if ($this->customerId > 0) {
                $this->checkoutMethod = "customer";
            }
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Set Billing Data in Quote
     *
     * @param Magento\Model\Quote\Model $quote quote
     *
     * @return void
     */
    public function setBillingDataInQuote($quote)
    {
        $result["error"] = false;

        if (!empty($this->billingData)) {
            $saveInAddressBook = 0;
            if (isset($this->billingData["newAddress"]["saveInAddressBook"])) {
                $saveInAddressBook = $this->billingData["newAddress"]["saveInAddressBook"];
            }
            if ($this->checkoutMethod == "register") {
                $saveInAddressBook = 1;
            }
            if (isset($this->billingData["sameAsShipping"]) && $this->billingData["sameAsShipping"] > 0) {
                $sameAsShipping = $this->billingData["sameAsShipping"];
                $this->billingData["addressId"] = $quote->getShippingAddress()->getCustomerAddressId();
            } else {
                $sameAsShipping = 0;
            }

            $addressId = 0;
            if ($this->billingData["addressId"] != "") {
                $addressId = $this->billingData["addressId"];
            }

            $quote->setCheckoutMethod($this->checkoutMethod)->save();
            $newAddress = [];

            if ($this->billingData["newAddress"] != "") {
                if (!empty($this->billingData["newAddress"])) {
                    $newAddress = $this->billingData["newAddress"];
                }
            }

            $address = $quote->getBillingAddress();
            $addressForm = $this->customerForm;
            $addressForm->setFormCode("customer_address_edit")->setEntityType("customer_address");

            if ($addressId > 0) {
                $customerAddress = $this->customerAddress->load($addressId)->getDataModel();
                if ($customerAddress->getId()) {
                    if ($customerAddress->getCustomerId() != $quote->getCustomerId()) {
                        $returnArray["message"] = __("Customer Address is not valid.");
                        return $returnArray;
                    }
            
                    // Import basic customer address data
                    $address->importCustomerAddressData($customerAddress)->setSaveInAddressBook(0);
                    
                    // Fetch custom attributes from customerAddress
                    if ($customerAddress->getCustomAttributes()) {
                        foreach ($customerAddress->getCustomAttributes() as $attribute) {
                            $address->setData($attribute->getAttributeCode(), $attribute->getValue());
                        }
                    }
                    
                    $addressForm->setEntity($address);
                }
            } else {
                $addressForm->setEntity($address);
            
                $addressData = [
                    "fax" => $newAddress["fax"] ?? "",
                    "city" => $newAddress["city"] ?? "",
                    "region" => $newAddress["region"],
                    "prefix" => $newAddress["prefix"] ?? "",
                    "suffix" => $newAddress["suffix"] ?? "",
                    "street" => $newAddress["street"],
                    "company" => $newAddress["company"],
                    "lastname" => $newAddress["lastName"],
                    "postcode" => $newAddress["postcode"],
                    "firstname" => $newAddress["firstName"],
                    "region_id" => $newAddress["region_id"],
                    "telephone" => $newAddress["telephone"],
                    "middlename" => $newAddress["middleName"] ?? "",
                    "country_id" => $newAddress["country_id"],
                    "address_title" => $newAddress["address_title"] ?? ""
                ];
            
                // Handle dynamic custom attributes
                if (isset($newAddress['custom_attributes']) && is_array($newAddress['custom_attributes'])) {
                    foreach ($newAddress['custom_attributes'] as $customAttribute) {
                        if (isset($customAttribute['attribute_code']) && isset($customAttribute['value'])) {
                            $addressData[$customAttribute['attribute_code']] = $customAttribute['value'];
                        }
                    }
                }
            
                $addressForm->compactData($addressData);
                $address->setCustomerAddressId(null);
                $address->setSaveInAddressBook($saveInAddressBook);
            
                $quote->setCustomerFirstname($newAddress["firstName"])->setCustomerLastname($newAddress["lastName"]);
            }
            

            if (in_array($this->checkoutMethod, ["register", "guest"])) {
                if (!empty($newAddress["email"]) && !$this->validateEmail->isValid($newAddress["email"], "EmailAddress")) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Invalid email format")
                    );
                }
                $quote->setCustomerEmail(trim($newAddress["email"]));
                $address->setEmail(trim($newAddress["email"]));
            }

            if (!$address->getEmail() && $quote->getCustomerEmail()) {
                $address->setEmail($quote->getCustomerEmail());
            }

            if (($validateRes = $address->validate()) !== true) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(implode(",", $validateRes))
                );
            }

            if (true !== ($result = $this->_validateCustomerData($this->wholeData))) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(implode(",", $result))
                );
            }

            if (!$quote->getCustomerId() && "register" == $quote->getCheckoutMethod()) {
                if ($this->_customerEmailExists($address->getEmail(), $this->storeManager->getStore()->getWebsiteId())) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("This email already exists.")
                    );
                }
            }

            if (!$quote->isVirtual()) {
                $usingCase = isset($sameAsShipping) ? (int)$sameAsShipping : 0;
                switch ($usingCase) {
                    case 0:
                        $shipping = $quote->getShippingAddress();
                        $shipping->setSameAsBilling(0);
                        $setStepDataShipping = 0;
                        break;
                    case 1:
                        $billing = clone $address;
                        $billing->unsAddressId()->unsAddressType();
                        $shipping = $quote->getShippingAddress();
                        $shippingMethod = $shipping->getShippingMethod();
                        $shipping->addData($billing->getData())
                            ->setSameAsBilling(1)
                            ->setSaveInAddressBook(0)
                            ->setShippingMethod($shippingMethod)
                            ->setCollectShippingRates(true);
                        $setStepDataShipping = 1;
                        break;
                }
            }

            $quote->collectTotals()->save();
            if (!$quote->isVirtual() && $setStepDataShipping) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
            }
        } else {
            $result["error"] = true;
            $result["message"] = __("Invalid Billing Data");
            return $result;
        }
    }

    /**
     * Function to save payment Info
     *
     * @param \Magento\Quote\Model\Quote $quote quote
     *
     * @return \Magento\Quote\Model\Quote $quote quote
     */
    public function savePaymentInfo($quote)
    {
        //saving payment //////////////////////////////////////////////////////////////////
        if ($this->method != "") {
            $paymentData = [];
            $paymentData["method"] = $this->method;
            if ($this->cc_cid != "") {
                $paymentData["cc_cid"] = $this->cc_cid;
            }
            if ($this->cc_exp_month != "") {
                $paymentData["cc_exp_month"] = $this->cc_exp_month;
            }
            if ($this->cc_exp_year != "") {
                $paymentData["cc_exp_year"] = $this->cc_exp_year;
            }
            if ($this->cc_number != "") {
                $paymentData["cc_number"] = $this->cc_number;
            }
            if ($this->cc_type != "") {
                $paymentData["cc_type"] = $this->cc_type;
            }
            if ($this->poNumber != "") {
                $paymentData["po_number"] = $this->poNumber;
            }
            if ($quote->isVirtual()) {
                $quote->getBillingAddress()->setPaymentMethod(isset($this->method) ? $this->method : null);
            } else {
                $quote->getShippingAddress()->setPaymentMethod(isset($this->method) ? $this->method : null);
            }
            if (!$quote->isVirtual() && $quote->getShippingAddress()) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
            }
            try {
                $paymentData[""] = [AbstractMethod::CHECK_USE_CHECKOUT,
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                AbstractMethod::CHECK_ZERO_TOTAL];
                $payment = $quote->getPayment()->importData($paymentData);
                $quote->save();
            } catch (\Exception $e) {
                $this->returnArray["message"] = $e->getMessage(). "";
                $this->helper->printLog($this->returnArray);
                return $this->returnArray;
            }
        }
    }

    /**
     * Function to save Mobikul Order
     *
     * @param Magento\Sales\Mode\Order $order object of order
     *
     * @return void;
     */
    public function saveMobikulOrder($order)
    {
        $customerName  = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
        $this->mobikulOrder->create()
            ->setOrderId($order->getId())
            ->setRealOrderId($order->getRealOrderId())
            ->setStoreId($order->getStoreId())
            ->setCustomerId($order->getCustomerId())
            ->setCustomerName($customerName)
            ->setOrderTotal($order->getGrandTotal())
            ->setCreatedAt($order->getCreatedAt())
            ->save();
    }

    /**
     * Function to save purchase point detail of Order
     *
     * @param Magento\Sales\Mode\Order $order object of order
     *
     * @return void;
     */
    public function savePurchasePointDetail($order)
    {
        $this->helper->setPurchasePoint($this->purchasePoint, $order);
    }
}
