<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Controller\Transfer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\Walletsystem\Model\WalletUpdateData;
use Webkul\Walletsystem\Model\Wallettransaction;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Webkul Walletsystem Class
 */
class Addpayee extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $walletHelper;

    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $walletUpdate;

    /**
     * @var  CustomerFactory
     */
    protected $customerModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var WalletPayeeFactory
     */
    protected $walletPayee;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Webkul\Walletsystem\Model\WalletNotification
     */
    protected $walletNotification;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Initialize dependencies
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param WalletUpdateData $walletUpdate
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Webkul\Walletsystem\Model\WalletNotification $walletNotification
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        WalletUpdateData $walletUpdate,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\Walletsystem\Model\WalletNotification $walletNotification,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->walletHelper = $walletHelper;
        $this->walletUpdate = $walletUpdate;
        $this->customerModel = $customerModel;
        $this->walletNotification = $walletNotification;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->jsonHelper = $jsonHelper;
        $this->walletPayee = $walletPayee;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Controller execute function
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $result = [
                'backUrl' => $this->_url->getUrl('customer/account/login')
            ];
            return $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($result)
            );
        }
        $params = $this->getRequest()->getParams();
        $result = $this->validateParams($params);
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath(
                'walletsystem/transfer/index',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($result)
        );
    }

    /**
     * Validate params
     *
     * @param array $params
     * @return array $result
     */
    protected function validateParams($params)
    {

        $result = [
            'error' => 0
        ];
        $errors = 0;
        if (isset($params) && is_array($params) && !empty($params)
        && !preg_match('#<script(.*?)>(.*?)</script>#is', $params['nickname'])
        ) {
            $errors = $this->validateParamArray($params);
            if ($errors==1) {
                $result['error'] = 1;
                $result['error_msg'] = __("Please try again later");
                return $result;
            }
            $customer = $this->customerModel->create();
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            if (isset($websiteId)) {
                $customer->setWebsiteId($websiteId);
            }
            $customer->loadByEmail($params['customer_email']);
            if ($customer && $customer->getId()) {
                if ($customer->getId() == $params['customer_id']) {
                    $result['error_msg'] = __("You can not add yourself in your payee list.");
                    $result['error'] = 1;
                } elseif ($this->alreadyAddedInPayee($params, $customer)) {
                    $result['error_msg'] =
                    __(
                        "Customer with %1 email address id already present in payee list",
                        $params['customer_email']
                    );
                    $result['error'] = 1;
                } else {
                    $result = $this->addPayeeToCustomer($params, $customer);
                }
            } else {
                $result['error_msg'] = __(
                    "No customer found with email address %1",
                    $params['customer_email']
                );
                $result['error'] = 1;
            }
        } else {
            $result['error'] = 1;
            $result['error_msg'] = __(
                "Data is not validate"
            );
            $this->messageManager->addError(__("Data is not validate"));
        }
        return $result;
    }

    /**
     * Validate param array
     *
     * @param array $params
     * @return bool
     */
    public function validateParamArray($params)
    {
        $error = 0;
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'customer_id':
                    if ($value == '') {
                        $error = 1;
                    }
                    break;
                case 'customer_email':
                    if ($value == '') {
                        $error = 1;
                    }
                    break;
            }
        }
        return $error;
    }

    /**
     * Add payee to customer
     *
     * @param array $params
     * @param object $customer
     * @return array
     */
    public function addPayeeToCustomer($params, $customer)
    {
        $payeeModel = $this->walletPayee->create();
        $configStatus = $this->walletHelper->getPayeeStatus();
        if (!$configStatus) {
            $status = $payeeModel::PAYEE_STATUS_ENABLE;
        } else {
            $status = $payeeModel::PAYEE_STATUS_DISABLE;
        }
        $payeeModel->setCustomerId($params['customer_id'])
            ->setNickName($params['nickname'])
            ->setPayeeCustomerId($customer->getEntityId())
            ->setStatus($status)
            ->setWebsiteId($customer->getWebsiteId())
            ->save();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $payeeApprovalRequired = $this->scopeConfig->getValue(
            'walletsystem/transfer_settings/payeestatus',
            $storeScope
        );
        if ($payeeApprovalRequired) {
            $this->setNotificationMessageForAdmin();
        }
        if ($payeeApprovalRequired) {
            $displayCustomMessage = $this->scopeConfig->getValue(
                'walletsystem/transfer_settings/show_payee_message',
                $storeScope
            );
            if ($displayCustomMessage) {
                $message = __($this->scopeConfig->getValue(
                    'walletsystem/transfer_settings/show_payee_message_content',
                    $storeScope
                ));
            }
        }
        if (!isset($message)) {
            $message = __("Payee is added in your list");
        }
        $this->messageManager->addSuccess($message);
        $result = [
            'error' => 0,
            'success_msg' => __('Payee is added in your list'),
            'backUrl' => $this->_url->getUrl('walletsystem/transfer/index')
        ];
        return $result;
    }

    /**
     * Already added in payee
     *
     * @param array $params
     * @param object $customer
     * @return boolean
     */
    public function alreadyAddedInPayee($params, $customer)
    {
        $payeeModel = $this->walletPayee->create()->getCollection()
            ->addFieldToFilter('customer_id', $params['customer_id'])
            ->addFieldToFilter('payee_customer_id', $customer->getEntityId())
            ->addFieldToFilter('website_id', $customer->getWebsiteId());
        if ($payeeModel->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * Set notification message for admin
     */
    public function setNotificationMessageForAdmin()
    {
        $notificationModel = $this->walletNotification->getCollection();
        if (!$notificationModel->getSize()) {
            $this->walletNotification->setPayeeCounter(1);
            $this->walletNotification->save();
        } else {
            foreach ($notificationModel->getItems() as $notification) {
                $notification->setPayeeCounter($notification->getPayeeCounter()+1);
            }
        }
        $notificationModel->save();
    }
}
