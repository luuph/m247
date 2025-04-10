<?php
namespace Magecomp\Whatsappultimate\Model;

use Magento\Email\Model\Template\Filter;
use Magecomp\Whatsappultimate\Helper\Apicall;
use Magecomp\Whatsappultimate\Helper\Customer;
use Magecomp\Whatsappultimate\Helper\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magecomp\Whatsappultimate\Helper\Invoice;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magecomp\Whatsappultimate\Helper\Shipment;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magecomp\Whatsappultimate\Helper\Creditmemo;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magecomp\Whatsappultimate\Model\WhatsappultimateFactory;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magecomp\Whatsappultimate\Helper\Contact;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magecomp\Whatsappcountryflag\Helper\Data as CountryflagHelper;
use Magecomp\Whatsappultimate\Helper\Data as UltimateflagHelper;
use Magento\Store\Api\StoreRepositoryInterface;

class Ultimatepost implements \Magecomp\Whatsappultimate\Api\UltimateInterface
{
   /**
    * @var Apicall
    */
    protected $helperapi;
    /**
     * @var Customer
     */
    protected $helpercustomer;
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var Order
     */
    protected $helperorder;
    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;
    /**
     * @var Invoice
     */
    protected $helperinvoice;
    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;
    /**
     * @var Shipment
     */
    protected $helpershipment;
    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;
    /**
     * @var Creditmemo
     */
    protected $helpercreditmemo;
    /**
     * @var WhatsappultimateFactory
     */
    protected $smsmodel;
    /**
     * @var CustomerModel
     */
    protected $customerModel;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;
    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var AccountManagementInterface
     */
      protected $accountManagement;

    /**
     * @var Contact
     */
    protected $helpercontact;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    protected $helpercountryflag;
    protected $helperultimateflag;
    protected $StoreRepositoryInterface;
    protected $country;
    
    public function __construct(
        Apicall $helperapi,
        Customer $helpercustomer,
        Filter $filter,
        OrderRepositoryInterface $orderRepository,
        CustomerFactory $customerFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        Invoice $helperinvoice,
        Order $helperorder,
        ShipmentRepositoryInterface $shipmentRepository,
        Shipment $helpershipment,
        WhatsappultimateFactory $smsmodel,
        CreditmemoRepositoryInterface $creditmemoRepository,
        Creditmemo $helpercreditmemo,
        CustomerModel $customerModel,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        Contact $helpercontact,
        AccountManagementInterface $accountManagement,
        CountryflagHelper $helpercountryflag,
        UltimateflagHelper $helperultimateflag,
        StoreRepositoryInterface $StoreRepositoryInterface,
        \Magecomp\Whatsappcountryflag\Model\Config\Source\Country $country
    ) {
        $this->helperapi = $helperapi;
        $this->helpercustomer = $helpercustomer;
        $this->filter = $filter;
        $this->orderRepository = $orderRepository;
        $this->customerFactory = $customerFactory;
        $this->helperorder = $helperorder;
        $this->invoiceRepository = $invoiceRepository;
        $this->helperinvoice = $helperinvoice;
        $this->shipmentRepository = $shipmentRepository;
        $this->helpershipment = $helpershipment;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->smsmodel = $smsmodel;
        $this->customerModel = $customerModel;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->helpercontact = $helpercontact;
        $this->collectionFactory = $collectionFactory;
        $this->helpercreditmemo = $helpercreditmemo;
        $this->accountManagement = $accountManagement;
        $this->helpercountryflag = $helpercountryflag;
        $this->helperultimateflag = $helperultimateflag;
        $this->StoreRepositoryInterface = $StoreRepositoryInterface;
        $this->country = $country;
    }
     
    public function sendRegotp($mobilenumber, $countrycode, $isresend, $storeid)
    {
        try {
            // Check required parameters.
            if (empty($mobilenumber) || empty($countrycode)) {
                if (empty($mobilenumber)) {
                    $response = [
                        "status"  => false,
                        "message" => __("Please, Enter valid whatsApp number.")
                    ];
                } else {
                    $response = [
                        "status"  => false,
                        "message" => __("Invalid parameter list.")
                    ];
                }
                return [
                    'message' => $response['message'],
                    'success' => $response['status']
                ];
            }
    
            // Check if the extension is enabled.
            if (!$this->helperorder->isEnabled($storeid)) {
                $response = [
                    "status"  => false,
                    "message" => __("Please enable extension.")
                ];
                return [
                    'message' => $response['message'],
                    'success' => $response['status']
                ];
            }
    
            // Country flag validation.
            if ($this->helperultimateflag->isCountryFlagEnabled($storeid)) {
                $countrydigit = $this->helpercountryflag->getCountryvalidation($countrycode, $storeid);
                if (empty($countrycode) || $countrycode == "string" || empty($countrydigit)) {
                    $response = [
                        "status"  => false,
                        "message" => __("Please Enable Extension.")
                    ];
                    return [
                        'message' => $response['message'],
                        'success' => $response['status']
                    ];
                }
                if ($countrydigit != strlen($mobilenumber)) {
                    $response = [
                        "status"  => false,
                        "message" => __("Your WhatsApp Number must be " . $countrydigit . " digit long.")
                    ];
                    return [
                        'message' => $response['message'],
                        'success' => $response['status']
                    ];
                }
                $countryid    = $this->helpercountryflag->getCountryCode($countrycode);
                $mobilenumber = $countryid . $mobilenumber;
            }
    
            // Check if the mobile number is already verified.
            $customerModel = $this->customerFactory->create();
            $customercollection = $customerModel->getCollection();
            $customercollection->addFieldToFilter('mobilenumber', $mobilenumber);
            if ($customercollection->count() > 0) {
                $response = [
                    "status"  => false,
                    "message" => __("Your WhatsApp Number is already verified.")
                ];
                return [
                    'message' => $response['message'],
                    'success' => $response['status']
                ];
            }
    
            // Prepare OTP and message template.
            $usertmp      = $this->helpercustomer->getSignUpConfirmationUserTempId($storeid);
            $langcode     = $this->helpercustomer->getSignUpConfirmationUserLangcode($storeid);
            $params       = $this->helpercustomer->getSignUpConfirmationUserParams($storeid);
            $otp          = $this->helpercustomer->getOtp($storeid);
            $this->filter->setVariables(['otp' => $otp]);
            $messageTpl   = $this->helpercustomer->getSignUpConfirmationUserTemplate($storeid);
            $finalmessage = $this->filter->filter($messageTpl);
    
            // Prepare additional parameters for API call.
            $csid = $this->helpercustomer->getSignUpConfirmationUserTemplateSID($storeid);
            $json = json_encode(['name' => $otp]);
    
            // Call external API.
            $apiResponse = $this->helperapi->callApiUrl($mobilenumber, $finalmessage, null, $json, $csid);
    
            // If API call was successful, save the OTP.
            if ($apiResponse === true) {
                $smsModel = $this->smsmodel->create();
                $smscollection = $smsModel->getCollection();
                $smscollection->addFieldToFilter('mobile_number', $mobilenumber);
                if (count($smscollection) > 0) {
                    $smsModel = $this->smsmodel->create()->load($mobilenumber, 'mobile_number');
                }
                $smsModel->setMobileNumber($mobilenumber)
                    ->setOtp($otp)
                    ->setIsverify(0)
                    ->save();
    
                $successMessage = !empty($isresend)
                    ? __("OTP Resend Successfully.")
                    : __("OTP Send Successfully.");
                $response = [
                    'status'  => true,
                    'message' => __($successMessage)
                ];
            } else {
                $response = [
                    'status'  => false,
                    'message' => __("Something went wrong.")
                ];
            }
    
            return [
                'message' => $response['message'],
                'success' => $response['status']
            ];
        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage(),
                'success' => false
            ];
        }
    }
    
    
    public function verifyRegotp($mobilenumber, $countrycode, $otp, $storeid)
    {
        try {
            // Validate required parameters.
            if (empty($mobilenumber) || empty($countrycode) || empty($otp)) {
                if (empty($mobilenumber)) {
                    $response = [
                        "status"  => false,
                        "message" => __("Please, Enter valid whatsApp number.")
                    ];
                } else {
                    $response = [
                        "status"  => false,
                        "message" => __("Invalid parameter list.")
                    ];
                }
                return [
                    'message' => $response['message'],
                    'success' => $response['status']
                ];
            }
            
            // Check if extension is enabled.
            if (!$this->helperorder->isEnabled($storeid)) {
                $response = [
                    "status"  => false,
                    "message" => __("Please enable extension.")
                ];
                return [
                    'message' => $response['message'],
                    'success' => $response['status']
                ];
            }
            
            // Country flag validation.
            if ($this->helperultimateflag->isCountryFlagEnabled($storeid)) {
                $countrydigit = $this->helpercountryflag->getCountryvalidation($countrycode, $storeid);
                if (empty($countrycode) || $countrycode == "string" || empty($countrydigit)) {
                    $response = [
                        "status"  => false,
                        "message" => __("Please Enable the Extension")
                    ];
                    return [
                        'message' => $response['message'],
                        'success' => $response['status']
                    ];
                }
                if ($countrydigit != strlen($mobilenumber)) {
                    $response = [
                        "status"  => false,
                        "message" => __("Your WhatsApp Number must be " . $countrydigit . " digit long.")
                    ];
                    return [
                        'message' => $response['message'],
                        'success' => $response['status']
                    ];
                }
                $countryid = $this->helpercountryflag->getCountryCode($countrycode);
                $mobilenumber = $countryid . $mobilenumber;
            }
            
            // Verify OTP using SMS collection.
            if ($this->helpercustomer->isSignUpConfirmationForUser($storeid)) {
                $smsModel = $this->smsmodel->create();
                $smscollection = $smsModel->getCollection()
                    ->addFieldToFilter('mobile_number', $mobilenumber)
                    ->addFieldToFilter('otp', $otp);
                if (count($smscollection) > 0) {
                    $response = [
                        'status'  => true,
                        'message' => __("OTP Verified Successfully.")
                    ];
                } else {
                    $response = [
                        'status'  => false,
                        'message' => __("Invalid OTP or Mobile Number.")
                    ];
                }
            } else {
                $response = [
                    'status'  => false,
                    'message' => __("This service is disabled right now.")
                ];
            }
            
            return [
                'message' => $response['message'],
                'success' => $response['status']
            ];
        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage(),
                'success' => false
            ];
        }
    }
    
    
    public function sendMobileUpdateOtp($newmobilenumber, $oldmobilenumber, $countrycode, $customer_id, $isresend, $storeid)
    {
        try {
            if (empty($newmobilenumber) || empty($oldmobilenumber) || empty($customer_id) || empty($countrycode)) {
                $response = [
                    "status"  => false,
                    "message" => __("Invalid parameter list.")
                ];
                return [
                    'message' => $response['message'],
                    'success' => $response['status']
                ];
            }
            if (!$this->helperorder->isEnabled($storeid)) {
                $response = [
                    "status"  => false,
                    "message" => __("Please enable extension.")
                ];
                return [
                    'message' => $response['message'],
                    'success' => $response['status']
                ];
            }
            if ($this->helperultimateflag->isCountryFlagEnabled($storeid)) {
                $countrydigit = $this->helpercountryflag->getCountryvalidation($countrycode, $storeid);
                if (empty($countrycode) || $countrycode == "string" || empty($countrydigit)) {
                    $response = [
                        "status"  => false,
                        "message" => __("Invalid country code")
                    ];
                    return [
                        'message' => $response['message'],
                        'success' => $response['status']
                    ];
                }
                if ($countrydigit != strlen($oldmobilenumber)) {
                    $response = [
                        "status"  => false,
                        "message" => __("Your old WhatsApp Number must be " . $countrydigit . " digit long.")
                    ];
                    return [
                        'message' => $response['message'],
                        'success' => $response['status']
                    ];
                }
                if ($countrydigit != strlen($newmobilenumber)) {
                    $response = [
                        "status"  => false,
                        "message" => __("Your new WhatsApp Number must be " . $countrydigit . " digit long.")
                    ];
                    return [
                        'message' => $response['message'],
                        'success' => $response['status']
                    ];
                }
                $countryid       = $this->helpercountryflag->getCountryCode($countrycode);
                $newmobilenumber = $countryid . $newmobilenumber;
                $oldmobilenumber = $countryid . $oldmobilenumber;
            }
    
            $customerModel = $this->customerFactory->create();
            $customercollection = $customerModel->getCollection();
            $customercollection->addFieldToFilter('mobilenumber', $oldmobilenumber)
                               ->addAttributeToFilter('entity_id', $customer_id);
    
            $customernewModel = $this->customerFactory->create();
            $customernewcollection = $customernewModel->getCollection();
            $customernewcollection->addFieldToFilter('mobilenumber', $newmobilenumber);
    
            if ($customercollection->count() <= 0) {
                $response = [
                    "status"  => false,
                    "message" => __("Customer does not exist.")
                ];
                return [
                    'message' => $response['message'],
                    'success' => $response['status']
                ];
            }
            if ($newmobilenumber == $oldmobilenumber) {
                $response = [
                    "status"  => false,
                    "message" => __("Your WhatsApp Number is already verified.")
                ];
                return [
                    'message' => $response['message'],
                    'success' => $response['status']
                ];
            }
            if ($customernewcollection->count() >= 1) {
                $response = [
                    "status"  => false,
                    "message" => __("Your New WhatsApp Number is already verified with other user Account.")
                ];
                return [
                    'message' => $response['message'],
                    'success' => $response['status']
                ];
            }
    
            $usertmp = $this->helpercustomer->getSignUpConfirmationUserTempId($storeid);
            $langcode = $this->helpercustomer->getSignUpConfirmationUserLangcode($storeid);
            $params = $this->helpercustomer->getSignUpConfirmationUserParams($storeid);
            $otp = $this->helpercustomer->getOtp($storeid);
    
            $this->filter->setVariables(['otp' => $otp]);
            $message = $this->helpercustomer->getMobileConfirmationUserTemplate($storeid);
            $finalmessage = $this->filter->filter($message);
        
            $json = json_encode(['name' => $otp]);
            $csid = $this->helpercustomer->getMyAccountConfirmationUserTemplateSID($storeid);
    
            $apiResponse = $this->helperapi->callApiUrl($newmobilenumber, $finalmessage, $storeid, $json, $csid);
    
            if ($apiResponse === true) {
                $smsModel = $this->smsmodel->create();
                $smscollection = $smsModel->getCollection();
                $smscollection->addFieldToFilter('mobile_number', $newmobilenumber);
    
                if (count($smscollection) > 0) {
                    $smsModel = $this->smsmodel->create()->load($newmobilenumber, 'mobile_number');
                }
                $smsModel->setMobileNumber($newmobilenumber)
                         ->setOtp($otp)
                         ->setIsverify(0)
                         ->save();
    
                $successMessage = !empty($isresend)
                    ? __("OTP Resend Successfully.")
                    : __("OTP Send Successfully.");
                $response = [
                    'status'  => true,
                    'message' => __($successMessage)
                ];
            } else {
                $response = [
                    'status'  => false,
                    'message' => __("Something went wrong.")
                ];
            }
    
            return [
                'message' => $response['message'],
                'success' => $response['status']
            ];
        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage(),
                'success' => false
            ];
        }
    }
    
    public function verifyMobileUpdateOtp($newmobilenumber, $oldmobilenumber, $countrycode, $customer_id, $otp, $storeid)
    {
        try {
            // Validate required parameters.
            if (empty($countrycode) || empty($newmobilenumber) || empty($oldmobilenumber) || empty($customer_id) || empty($otp)) {
                $response = ['status' => false, 'message' => __("Invalid parameter list.")];
                return ['message' => $response['message'], 'success' => $response['status']];
            }
            // Check if the extension is enabled.
            if (!$this->helperorder->isEnabled($storeid)) {
                $response = ['status' => false, 'message' => __("Please enable extension.")];
                return ['message' => $response['message'], 'success' => $response['status']];
            }
            // Country flag validation.
            if ($this->helperultimateflag->isCountryFlagEnabled($storeid)) {
                $countrydigit = $this->helpercountryflag->getCountryvalidation($countrycode, $storeid);
                if (empty($countrycode) || $countrycode == "string" || empty($countrydigit)) {
                    $response = ['status' => false, 'message' => __("Invalid country code")];
                    return ['message' => $response['message'], 'success' => $response['status']];
                }
                if ($countrydigit != strlen($oldmobilenumber)) {
                    $response = ['status' => false, 'message' => __("Your old WhatsApp Number must be " . $countrydigit . " digit long.")];
                    return ['message' => $response['message'], 'success' => $response['status']];
                }
                if ($countrydigit != strlen($newmobilenumber)) {
                    $response = ['status' => false, 'message' => __("Your new WhatsApp Number must be " . $countrydigit . " digit long.")];
                    return ['message' => $response['message'], 'success' => $response['status']];
                }
                $countryid = $this->helpercountryflag->getCountryCode($countrycode);
                $newmobilenumber = $countryid . $newmobilenumber;
                $oldmobilenumber = $countryid . $oldmobilenumber;
            }
            // Verify that the customer exists using the old mobile number.
            $customerModel = $this->customerFactory->create();
            $customercollection = $customerModel->getCollection();
            $customercollection->addFieldToFilter('mobilenumber', $oldmobilenumber)
                               ->addAttributeToFilter('entity_id', $customer_id);
            if ($customercollection->count() <= 0) {
                $response = ['status' => false, 'message' => __("Customer does not exist.")];
                return ['message' => $response['message'], 'success' => $response['status']];
            }
            // Check if the new number is identical to the old.
            if ($newmobilenumber == $oldmobilenumber) {
                $response = ['status' => false, 'message' => __("Your WhatsApp Number is already verified.")];
                return ['message' => $response['message'], 'success' => $response['status']];
            }
            // Get the SMS record using the new mobile number and OTP.
            $smsModel = $this->smsmodel->create();
            $smscollection = $smsModel->getCollection();
            $smscollection->addFieldToFilter('mobile_number', $newmobilenumber)
                          ->addFieldToFilter('otp', $otp);
            // Iterate over the SMS collection.
            foreach ($smscollection as $smsdata) {
                // Build search criteria to find the customer with the old mobile number.
                $mobileFilter = $this->filterBuilder
                    ->setValue($oldmobilenumber)
                    ->setConditionType('eq')
                    ->setField('mobilenumber')
                    ->create();
                $mobileFilterGroup = $this->filterGroupBuilder->setFilters([$mobileFilter])->create();
                $searchCriteria = $this->searchCriteriaBuilder->create();
                $searchCriteria->setFilterGroups([$mobileFilterGroup]);
    
                $customerRepository = $this->customerRepository->getList($searchCriteria);
                if ($customerRepository->getItems()) {
                    $currCustomer = current($customerRepository->getItems());
                    $customer = $this->customerModel->load($currCustomer->getId());
                    $customerData = $customer->getDataModel();
                    $customerData->setCustomAttribute('mobilenumber', $newmobilenumber);
                    $customer->updateData($customerData);
                    $customer->save();
    
                    // Remove the used SMS record.
                    $smsdata->delete();
    
                    $response = ['status' => true, 'message' => __("WhatsApp Number Updated Successfully.")];
                    return ['message' => $response['message'], 'success' => $response['status']];
                } else {
                    $response = ['status' => false, 'message' => __("Current WhatsApp Number is not exist.")];
                    return ['message' => $response['message'], 'success' => $response['status']];
                }
            }
            // If no matching SMS record was found.
            $response = ['status' => false, 'message' => __("Invalid WhatsApp Number Or OTP.")];
            return ['message' => $response['message'], 'success' => $response['status']];
        } catch (\Exception $e) {
            $response = ['status' => false, 'message' => $e->getMessage()];
            return ['message' => $response['message'], 'success' => $response['status']];
        }
    }
    

    public function sendRegistrationNotification(
        $email,
        $password,
        $mobilenumber,
        $countrycode,
        $otp,
        $storeId
    ) {
        try {

            if (empty($password) || empty($email) || empty($mobilenumber) || empty($countrycode) || empty($otp) || empty($storeId)) {
            
                $response = ["status"=>false, "message"=>__("Invalid parameter list.")];
                return json_encode($response);
            }
            if (!$this->helperorder->isEnabled($storeId)) {
                $response = [
                    "status"=>false,
                    'message' => __("Please enable extension.")
                ];
                return json_encode($response);
            }
            if ($this->helperultimateflag->isCountryFlagEnabled($storeId)) {
            
                $countrydigit=$this->helpercountryflag->getCountryvalidation($countrycode,$storeId);
                if (empty($countrycode) || $countrycode=="string" || empty($countrydigit)) {
                    $response = ["status"=>false, "message"=>__("Invalid country code")];
                    return json_encode($response);
                }
                if ($countrydigit != strlen($mobilenumber)) {
                        $response = ["status"=>false, "message"=>__("Your WhatsApp Number must be ".$countrydigit." digit long.")];
                       return json_encode($response);
                }
                $countryid = $this->helpercountryflag->getCountryCode($countrycode);
                $mobilenumber=$countryid.$mobilenumber;
            }

            $mobileFilter = $this->filterBuilder
                ->setValue($email)
                ->setConditionType('eq')
                ->setField('email')->create();
        
            $mobileFilterGroup = $this->filterGroupBuilder->setFilters([$mobileFilter])->create();
        
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $searchCriteria->setFilterGroups([$mobileFilterGroup]);

            $customerRepository = $this->customerRepository->getList($searchCriteria);
        
            if($customerRepository->getItems()) {
        
                $smsModel = $this->smsmodel->create();
        
                $smscollection = $smsModel->getCollection()
                    ->addFieldToFilter('mobile_number', $mobilenumber)
                    ->addFieldToFilter('otp', $otp);
                if ($smscollection->count() >= 0) 
                {
                    $currCustomer = current($customerRepository->getItems());
                    
                    $customer = $this->customerModel->load($currCustomer->getId());
                
                    $this->filter->setVariables([
                        'customer' => $customer,
                        'mobilenumber' => $mobilenumber
                    ]);

                    $json = json_encode([
                        'firstname' => $customer->getData('firstname'),
                        'lastname' => $customer->getData('lastname'),
                        'email' => $customer->getData('email'),
                        'created_in' => (string)$customer->getData('created_at'),
                        'created_at' => (string)$customer->getData('created_at'),
                        'mobilenumber' => $customer
                    ]);
            
                    if($this->helpercustomer->isSignUpNotificationForAdmin($storeId) && $this->helpercustomer->getAdminNumber($storeId)) {
                        $message = $this->helpercustomer->getSignUpNotificationForAdminTemplate($storeId);
                        $finalmessage = $this->filter->filter($message);
                        // $this->helperapi->callApiUrl($this->helpercustomer->getAdminNumber($storeId), $finalmessage,$storeId);

                        $csid = $this->helpercustomer->getSignUpNotificationForAdminSID($storeId);
                        $this->helperapi->callApiUrl($this->helpercustomer->getAdminNumber($storeId), $finalmessage,$storeId=null,$json,$csid);
                    }
                
                    foreach ($smscollection as $sms) {
                    
                        $customerData = $customer->getDataModel();
                        $customerData->setCustomAttribute('mobilenumber', $mobilenumber);
                        $customer->updateData($customerData);
                        $customer->save();
                        $sms->delete();
                    }
                    if ($this->helpercustomer->isSignUpNotificationForUser($storeId)) {
                    
                        $message = $this->helpercustomer->getSignUpNotificationForUserTemplate($storeId);
                        $finalmessage = $this->filter->filter($message);
                        // $this->helperapi->callApiUrl($mobilenumber, $finalmessage, $langcode = null);
                        $csid = $this->helpercustomer->getSignUpNotificationForUserSID($storeId);

                        $this->helperapi->callApiUrl($this->helpercustomer->getAdminNumber($storeId), $finalmessage,$storeId=null,$json,$csid);
                    }
                
                    $response = ['status' => true,
                        'message' => __("Registration is Notified"),
                    ];
                } else {
                
                    $response = ['status' => false,
                        'message' => __("Invalid OTP.")];
                }
            
            } else {
            
                $response = ['status' => false,
                    'message' => __("Email is not exist.")];
            }

            return json_encode($response);
        } catch (\Exception $e) {
        
            $response = ['status' => false,
            'message' => $e->getMessage()
        ];
        return json_encode($response);
        }
    }
    
    public function sendOrderNotification($orderid, $isresend)
    {
        try {
            if (empty($orderid)) {
                   $response = ["status"=>false, "message"=>__("Please, enter order id.")];
                   return json_encode($response);
            }
                $order = $this->orderRepository->get($orderid);
                $storeId= $order->getStoreId();

                 if (!$this->helperorder->isEnabled($order->getStoreId())) {
                    $response = [
                        "status"=>false,
                        'message' => __("This service is disable right now.")
                    ];
                    return json_encode($response);
                }
            if ($order) {
                $billingAddress = $order->getBillingAddress();
                $mobilenumber = $billingAddress->getTelephone();

                if ($order->getCustomerId() > 0) {
                    $customer = $this->customerFactory->create()->load($order->getCustomerId());
                    $mobile = $customer->getMobilenumber();
                    if ($mobile != '' && $mobile != null) {
                        $mobilenumber = $mobile;
                    }

                    $this->filter->setVariables([
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
                    $this->filter->setVariables([
                        'order' => $order,
                        'order_total' => $order->formatPriceTxt($order->getGrandTotal()),
                        'mobilenumber' => $mobilenumber,
                        'customer' => $order->getBillingAddress()
                    ]);
                    $data['mobilenumber'] = $mobilenumber;
                }

                $storeId = $order->getStoreId();
                $json = json_encode($data);
                
                if ($this->helperorder->isOrderNotificationForUser($order->getStoreId())) {
                    $message = $this->helperorder->getOrderNotificationUserTemplate($order->getStoreId());
                    $finalmessage = $this->filter->filter($message);
                    // $this->helperapi->callApiUrl($mobilenumber, $finalmessage, $langcode = null);
                    $storeId = $order->getStoreId();
                    $sid = $this->helperorder->getOrderNotificationUserSID($storeId);
                    $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$sid);
                }

                if ($isresend && $this->helperorder->isOrderNotificationForAdmin($order->getStoreId()) && $this->helperorder->getAdminNumber($order->getStoreId())) {
                    $message = $this->helperorder->getOrderNotificationForAdminTemplate($order->getStoreId());
                    $finalmessage = $this->filter->filter($message);
                    // $this->helperapi->callApiUrl($this->helperorder->getAdminNumber($order->getStoreId()), $finalmessage,$order->getStoreId());
                    $storeId = $order->getStoreId();
                    $sid = $this->helperorder->getOrderNotificationForAdminSID($storeId);
                    $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$sid);
                }
                $response = ['status' => true,
                    'message' => __("Order is Notified")
                ];
            }
            return json_encode($response);
        } catch (\Exception $e) {
            $response = ['status' => false,
            'message' => $e->getMessage()
        ];
        return json_encode($response);
        }
    }

    public function sendInvoiceNotification($invoiceid, $isresend)
    {
        try {
            if (empty($invoiceid)) {
                   $response = ["status"=>false, "message"=>__("Please, enter invoice id.")];
                   return json_encode($response);
            }
            $invoice = $this->invoiceRepository->get($invoiceid);
            $order = $invoice->getOrder();
            if ($order) {
                if (!$this->helperinvoice->isEnabled($order->getStoreId())) {
                    $response = [
                        "status"=>false,
                        'message' => __("This service is disable right now."),
                    ];
                    return json_encode($response);
                }

                $billingAddress = $order->getBillingAddress();
                $mobilenumber = $billingAddress->getTelephone();

                if ($order->getCustomerId() > 0) {
                    $customer = $this->customerFactory->create()->load($order->getCustomerId());
                    $mobile = $customer->getMobilenumber();
                    if ($mobile != '' && $mobile != null) {
                        $mobilenumber = $mobile;
                    }

                    $this->filter->setVariables([
                        'order' => $order,
                        'invoice' => $invoice,
                        'customer' => $customer,
                        'invoice_total' => $order->formatPriceTxt($invoice->getGrandTotal()),
                        'mobilenumber' => $mobilenumber
                    ]);

                    $data['mobilenumber'] = $mobilenumber;

                    $data['customer_firsname'] = $customer->getFirstname();
                    $data['customer_lastname'] = $customer->getLastname();
                    $data['customer_email'] = $customer->getEmail();
                    $data['customer_created_at'] = $customer->getCreatedAt();

                } else {
                    $this->filter->setVariables([
                        'order' => $order,
                        'invoice' => $invoice,
                        'invoice_total' => $order->formatPriceTxt($invoice->getGrandTotal()),
                        'mobilenumber' => $mobilenumber,
                        'customer' => $order->getBillingAddress(),
                    ]);
                    $data['mobilenumber'] = $mobilenumber;
                }

                $storeId = $order->getStoreId();
                $json = json_encode($data);
                
                if ($this->helperinvoice->isInvoiceNotificationForUser($order->getStoreId())) {
                    $message = $this->helperinvoice->getInvoiceNotificationUserTemplate($order->getStoreId());
                    $storeId = $order->getStoreId();
                    $finalmessage = $this->filter->filter($message);
                    // $this->helperapi->callApiUrl($mobilenumber, $finalmessage, $langcode = null);
                    $csid = $this->helperinvoice->getInvoiceNotificationUserSID($storeId);
                    $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$csid);
                }

                if ($isresend && $this->helperinvoice->isInvoiceNotificationForAdmin($order->getStoreId()) && $this->helperinvoice->getAdminNumber($order->getStoreId())) {
                    $message = $this->helperinvoice->getInvoiceNotificationForAdminTemplate($order->getStoreId());
                    $finalmessage = $this->filter->filter($message);
                    $storeId = $order->getStoreId();
                    // $this->helperapi->callApiUrl($this->helperinvoice->getAdminNumber($order->getStoreId()), $finalmessage,$order->getStoreId());
                    $csid = $this->helperinvoice->getInvoiceNotificationForAdminSID($storeId);
                    $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$csid);
                }
                $response = ['status' => true,
                    'message' => __("Invoice is Notified")
                ];
            }
            return json_encode($response);
        } catch (\Exception $e) {
            $response = ['status' => false,
            'message' => $e->getMessage()
        ];
        return json_encode($response);
        }
    }

    public function sendShipmentNotification($shipmentid, $isresend)
    {
        try {
            if (empty($shipmentid)) {
                   $response = ["status"=>false, "message"=>__("Please, enter shipment id.")];
                   return json_encode($response);
            }
            $shipment = $this->shipmentRepository->get($shipmentid);
            $order = $shipment->getOrder();
            if ($order) {
                if (!$this->helpershipment->isEnabled($order->getStoreId())) {
                    $response = [
                        "status"=>false,
                        'message' => __("This service is disable right now."),
                    ];
                    return json_encode($response);
                }

                $billingAddress = $order->getBillingAddress();
                $mobilenumber = $billingAddress->getTelephone();

                if ($order->getCustomerId() > 0) {
                    $customer = $this->customerFactory->create()->load($order->getCustomerId());
                    $mobile = $customer->getMobilenumber();
                    if ($mobile != '' && $mobile != null) {
                        $mobilenumber = $mobile;
                    }

                    $this->filter->setVariables([
                        'order' => $order,
                        'shipment' => $shipment,
                        'customer' => $customer,
                        'mobilenumber' => $mobilenumber
                    ]);

                    $data['mobilenumber'] = $mobilenumber;
                    $data['customer_firsname'] = $customer->getFirstname();
                    $data['customer_lastname'] = $customer->getLastname();
                    $data['customer_email'] = $customer->getEmail();
                    $data['customer_created_at'] = $customer->getCreatedAt();
                } else {
                    $this->filter->setVariables([
                        'order' => $order,
                        'shipment' => $shipment,
                        'mobilenumber' => $mobilenumber,
                        'customer' => $order->getBillingAddress()
                    ]);
                    $data['mobilenumber'] = $mobilenumber;
                }

                $storeId = $order->getStoreId();
                $json = json_encode($data);
                
                if ($this->helpershipment->isShipmentNotificationForUser($order->getStoreId())) {
                    $message = $this->helpershipment->getShipmentNotificationUserTemplate($order->getStoreId());
                    $finalmessage = $this->filter->filter($message);
                    // $this->helperapi->callApiUrl($mobilenumber, $finalmessage, $langcode = null);
                    $storeId = $order->getStoreId();
                    $csid = $this->helpershipment->getShipmentNotificationUserSID($storeId);
                    $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$csid);
                }

                if ($isresend && $this->helpershipment->isShipmentNotificationForAdmin($order->getStoreId()) && $this->helpershipment->getAdminNumber($order->getStoreId())) {
                    $message = $this->helpershipment->getShipmentNotificationForAdminTemplate($order->getStoreId());
                    $finalmessage = $this->filter->filter($message);
                    // $this->helperapi->callApiUrl($this->helpershipment->getAdminNumber($order->getStoreId()), $finalmessage,$order->getStoreId());
                    $storeId = $order->getStoreId();
                    $csid = $this->helpershipment->getShipmentNotificationForAdminSID($storeId);
                    $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$csid);
                }
                $response = ['status' => true,
                    'message' => __("Shipment is Notified")
                ];
            }
            return json_encode($response);
        } catch (\Exception $e) {
            $response = ['status' => false,
            'message' => $e->getMessage()
        ];
        return json_encode($response);
        }
    }

    public function sendCreditmemoNotification($creditmemoid, $isresend)
    {
        try {
            if (empty($creditmemoid)) {
                   $response = ["status"=>false, "message"=>__("Please, enter creditmemo id.")];
                   return json_encode($response);
            }
            $creditmemo = $this->creditmemoRepository->get($creditmemoid);
            $order = $creditmemo->getOrder();
            if ($order) {
                if (!$this->helpercreditmemo->isEnabled($order->getStoreId())) {
                    $response = [
                        "status"=>false,
                        'message' => __("This service is disable right now."),
                    ];
                    return json_encode($response);
                }

                $billingAddress = $order->getBillingAddress();
                $mobilenumber = $billingAddress->getTelephone();

                if ($order->getCustomerId() > 0) {
                    $customer = $this->customerFactory->create()->load($order->getCustomerId());
                    $mobile = $customer->getMobilenumber();
                    if ($mobile != '' && $mobile != null) {
                        $mobilenumber = $mobile;
                    }

                    $this->filter->setVariables([
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
                    $this->filter->setVariables([
                        'order' => $order,
                        'creditmemo' => $creditmemo,
                        'mobilenumber' => $mobilenumber,
                        'customer' => $order->getBillingAddress()
                    ]);
                    $data['mobilenumber'] = $mobilenumber;
                }

                $json = json_encode($data);

                $storeId = $order->getStoreId();
                          
                if ($this->helpercreditmemo->isCreditmemoNotificationForUser($order->getStoreId())) {
                    $message = $this->helpercreditmemo->getCreditmemoNotificationUserTemplate($order->getStoreId());
                    $finalmessage = $this->filter->filter($message);
                    // $this->helperapi->callApiUrl($mobilenumber, $finalmessage, $langcode = null);
                    $csid = $this->helpercreditmemo->getCreditmemoSID($storeId);
                    $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$csid);
                }

                if ($isresend && $this->helpercreditmemo->isCreditmemoNotificationForAdmin($order->getStoreId()) && $this->helpercreditmemo->getAdminNumber($order->getStoreId())) {
                    $message = $this->helpercreditmemo->getCreditmemoNotificationForAdminTemplate($order->getStoreId());
                    $finalmessage = $this->filter->filter($message);
                    // $this->helperapi->callApiUrl($this->helpercreditmemo->getAdminNumber($order->getStoreId()), $finalmessage,$order->getStoreId());
                    $csid = $this->helpercreditmemo->getCreditmemoAdminSID($storeId);
                    $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$csid);
                }
                $response = ['status' => true,
                    'message' => __("Creditmemo is Notified")
                ];
            }
            return json_encode($response);
        } catch (\Exception $e) {
            $response = ['status' => false,
            'message' => $e->getMessage()
        ];
        return json_encode($response);
        }
    }
    
    public function sendContactNotification($name, $email, $mobilenumber, $comment, $countrycode, $storeId)
    {
    
        try {
            if (empty($name) || empty($email) || empty($mobilenumber) || empty($comment) || empty($countrycode)) {
                $response = ["status"=>false, "message"=>__("Invalid parameter list.")];
                return json_encode($response);
            }
            if (!$this->helperorder->isEnabled($storeId)) {
                $response = [
                    "status"=>false,
                    'message' => __("Please enable extension.")
                ];
                return json_encode($response);
            }
            $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
            if (!preg_match($regex, $email)) {
                $response = ["status" => false, "errormessage" => __("Please enter proper email.")];
                return json_encode($response);
            }
            if ($this->helperultimateflag->isCountryFlagEnabled($storeId)) {
                $countrydigit=$this->helpercountryflag->getCountryvalidation($countrycode,$storeId);
                if (empty($countrycode) || $countrycode=="string" || empty($countrydigit)) {
                    $response = ["status"=>false, "message"=>__("Invalid country code")];
                    return json_encode($response);
                }
                if ($countrydigit != strlen($mobilenumber)) {
                    $response = ["status"=>false, "message"=>__("Your WhatsApp Number must be ".$countrydigit." digit long.")];
                    return json_encode($response);
                }
                $countryid = $this->helpercountryflag->getCountryCode($countrycode);
                $mobilenumber=$countryid.$mobilenumber;
            }

            $this->filter->setVariables([
                'name' => $name,
                'email' => $email,
                'telephone' => $mobilenumber,
                'comment' => $comment,
                'store_name' => $this->helpercontact->getStoreName()
            ]);

            $json = json_encode([
                'name' => $name,
                'email' => $email,
                'telephone' => $mobilenumber,
                'comment' => $comment,
                'store_name' => $this->helpercontact->getStoreName(),
                'mobilenumber' => $mobilenumber
            ]);
                
            $usertmp = $this->helpercustomer->getSignUpConfirmationUserTempId($storeId);
            $langcode = $this->helpercustomer->getSignUpConfirmationUserLangcode($storeId);
            $params = $this->helpercustomer->getSignUpConfirmationUserParams($storeId);
            if ($this->helpercontact->isContactNotificationForUser($storeId)) {
                $message = $this->helpercontact->getContactNotificationUserTemplate($storeId);
                $finalmessage = $this->filter->filter($message);
                // $this->helperapi->callApiUrl($mobilenumber, $finalmessage, $storeId, $langcode, $usertmp, $params);
                $csid = $this->helpercontact->getContactSidTemplate($storeId);
                $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$csid);
            }
            
            if ($this->helpercontact->isContactNotificationForAdmin($storeId) && $this->helpercontact->getAdminNumber($storeId)) {
                $message = $this->helpercontact->getContactNotificationForAdminTemplate($storeId);
                $finalmessage = $this->filter->filter($message);
                // $this->helperapi->callApiUrl($this->helpercontact->getAdminNumber($storeId), $finalmessage, $storeId, $langcode, $usertmp, $params);
               
                $csid = $this->helpercontact->getContactSidAdmin($storeId);
                $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$csid);
            }
              $response = ['status' => true,
                'message' => __("Contact Information is Notified")
              ];

              return json_encode($response);
        } catch (\Exception $e) {
            $response = ['status' => false,
            'message' => $e->getMessage()
        ];
        return json_encode($response);
        }
    }
    public function getCountryList()
    {
        try {
            $response = ["status"=>true, "country"=>$this->getCountryListforAPI()];
            return [$response];
        } catch (\Exception $e) {
            $response = ["status"=>false, "message"=>__($e->getMessage())];
            return [$response];
        }
    }

    public function getCountryListforAPI()
    {
        $countryArray = $this->country->toOptionArray(); 
        $countryCodes = $this->getCountryCodeData();

        foreach ($countryArray as &$country) {
            $countryCode = $countryCodes[$country['value']] ?? null;
            $country['country_code'] = $countryCode;
        }

        return $countryArray;
    }

    public function getCountryCodeData()
    {
        return [
            'AF' => '93', 'AX' => '358', 'AL' => '355', 'DZ' => '213',
            'AS' => '1', 'AD' => '376', 'AO' => '244', 'AI' => '1',
            'AQ' => '672', 'AG' => '1', 'AR' => '54', 'AM' => '374',
            'AU' => '61', 'AT' => '43', 'AZ' => '994', 'BS' => '1',
            'BH' => '973', 'BD' => '880', 'BB' => '1', 'BY' => '375',
            'BE' => '32', 'BZ' => '501', 'BJ' => '229', 'BM' => '1',
            'BT' => '975', 'BO' => '591', 'BA' => '387', 'BW' => '267',
            'BR' => '55', 'BG' => '359', 'BF' => '226', 'BI' => '257',
            'KH' => '855', 'CM' => '237', 'CA' => '1', 'CV' => '238',
            'CF' => '236', 'TD' => '235', 'CL' => '56', 'CN' => '86',
            'CO' => '57', 'KM' => '269', 'CG' => '242', 'CD' => '243',
            'CR' => '506', 'CI' => '225', 'HR' => '385', 'CU' => '53',
            'CY' => '357', 'CZ' => '420', 'DK' => '45', 'DJ' => '253',
            'DM' => '1', 'DO' => '1', 'EC' => '593', 'EG' => '20',
            'SV' => '503', 'GQ' => '240', 'ER' => '291', 'EE' => '372',
            'ET' => '251', 'FI' => '358', 'FR' => '33', 'DE' => '49',
            'GH' => '233', 'GR' => '30', 'IN' => '91', 'US' => '1',
            'AW' => '297', 'BV' => null, 'IO' => '246', 'VG' => '1',
            'BN' => '673', 'BQ' => '599', 'KY' => '1',  'CX' => '61',
            'CC' => '61', 'CK' => '682', 'CW' => '599', 'SZ' => '268',
            'ET' => '251',  'FK' => '500', 'FO' => '298', 'FJ' => '679',
            'GF' => '594', 'PF' => '689', 'TF' => null, 'GA' => '241',
            'GM' => '220', 'GE' => '995', 'GI' => '350', 'GL' => '299',
            'GD' => '1', 'GP' => '590', 'GU' => '1', 'GT' => '502',
            'GG' => '44', 'GN' => '224', 'GW' => '245', 'GY' => '592',
            'HT' => '509', 'HM' => null, 'HN' => '504', 'HK' => '852',
            'HU' => '36', 'IS' => '354', 'ID' => '62', 'IR' => '98',
            'IQ' => '964', 'IE' => '353', 'IM' => '44', 'IL' => '972',
            'IT' => '39', 'JM' => '1', 'JP' => '81', 'JE' => '44',
            'JO' => '962', 'KZ' => '7', 'KE' => '254', 'KI' => '686',
            'KW' => '965', 'KG' => '996', 'LA' => '856', 'LV' => '371',
            'LB' => '961', 'LS' => '266', 'LR' => '231', 'LY' => '218',
            'LI' => '423', 'LT' => '370', 'LU' => '352', 'MO' => '853',
            'MG' => '261', 'MW' => '265', 'MY' => '60', 'MV' => '960',
            'ML' => '223', 'MT' => '356', 'MH' => '692', 'MQ' => '596',
            'MR' => '222', 'MU' => '230', 'YT' => '262', 'MX' => '52',
            'FM' => '691', 'MD' => '373', 'MC' => '377', 'MN' => '976',
            'ME' => '382', 'MS' => '1', 'MA' => '212', 'MZ' => '258',
            'MM' => '95', 'NA' => '264', 'NR' => '674', 'NP' => '977',
            'NL' => '31', 'NC' => '687', 'NZ' => '64', 'NI' => '505',
            'NE' => '227', 'NG' => '234', 'NU' => '683', 'NF' => '672',
            'KP' => '850', 'MK' => '389', 'MP' => '1', 'NO' => '47',
            'OM' => '968', 'PK' => '92', 'PW' => '680', 'PS' => '970',
            'PA' => '507', 'PG' => '675', 'PY' => '595', 'PE' => '51',
            'PH' => '63', 'PN' => '872', 'PL' => '48', 'PT' => '351',
            'PR' => '1', 'QA' => '974', 'RE' => '262', 'RO' => '40',
            'RU' => '7', 'RW' => '250', 'WS' => '685', 'SM' => '378',
            'ST' => '239', 'SA' => '966', 'SN' => '221', 'RS' => '381',
            'SC' => '248', 'SL' => '232', 'SG' => '65', 'SX' => '1',
            'SK' => '421', 'SI' => '386', 'SB' => '677', 'SO' => '252',
            'ZA' => '27', 'GS' => null, 'KR' => '82', 'SS' => '211',
            'ES' => '34', 'LK' => '94', 'BL' => '590', 'KN' => '1',
            'LC' => '1', 'MF' => '590', 'PM' => '508', 'VC' => '1',
            'SD' => '249', 'SR' => '597', 'SJ' => '47', 'SE' => '46',
            'CH' => '41', 'SY' => '963', 'TW' => '886', 'TJ' => '992',
            'TZ' => '255', 'TH' => '66', 'TL' => '670', 'TG' => '228',
            'TK' => '690', 'TO' => '676', 'TT' => '1', 'TN' => '216',
            'TR' => '90', 'TM' => '993', 'TC' => '1', 'TV' => '688',
            'UG' => '256', 'UA' => '380', 'AE' => '971', 'GB' => '44',
            'UM' => '1', 'UY' => '598', 'UZ' => '998', 'VU' => '678',
            'VA' => '379', 'VE' => '58', 'VN' => '84', 'WF' => '681',
            'EH' => '212', 'YE' => '967', 'ZM' => '260', 'ZW' => '263',
            'US' => '1'
        ];
    }

}
