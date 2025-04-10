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

declare(strict_types=1);

namespace Webkul\Walletsystem\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * AddPayee resolver, used for GraphQL request processing.
 */
class AddPayee implements ResolverInterface
{
    /**
     * @var transactioncollection
     */
    private $transactioncollection;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    private $walletHelper;
 
    /**
     * @var \Webkul\Walletsystem\Model\WalletPayeeFactory
     */
    private $walletPayee;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Webkul\Walletsystem\Model\WalletNotification
     */
    private $walletNotification;

    /**
     * Construct function
     *
     * @param CustomerFactory $customerFactory
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Webkul\Walletsystem\Model\WalletNotification $walletNotification
     */
    public function __construct(
        CustomerFactory $customerFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\Walletsystem\Model\WalletNotification $walletNotification
    ) {
        $this->customerFactory = $customerFactory;
        $this->walletHelper = $walletHelper;
        $this->walletPayee = $walletPayee;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->walletNotification = $walletNotification;
    }

    /**
     * Resolver method for GraphQL
     *
     * @param Field $field
     * @param object $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $responseMessage=[];
            $result ='';
            if ($args['customerId']) {

                $result = $this->validateParams($args);
                $responseMessage['message'] = $result;

            } else {
                $responseMessage['message'] = 'Customer ID Required';
            }
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
        return $responseMessage;
    }

     /**
      * Validate params
      *
      * @param array $params
      * @return bool
      */
    protected function validateParams($params)
    {
        $message ='';
        $result = [
            'error' => 0
        ];
        $error = 0;
        if (isset($params) && is_array($params) && !empty($params)
        && !preg_match('#<script(.*?)>(.*?)</script>#is', $params['nickName'])
        ) {
            $errors = $this->validateParamArray($params);
            if ($error==1) {
                $message = 'Please try again later';
            }
            $customer = $this->customerFactory->create();
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            if (isset($websiteId)) {
                $customer->setWebsiteId($websiteId);
            }
            $customer->loadByEmail($params['customerEmail']);

            if ($customer && $customer->getId()) {
                if ($customer->getId() == $params['customerId']) {
                    $message = 'You can not add yourself in your payee list.';
                } elseif ($this->alreadyAddedInPayee($params, $customer)) {
                    $message =  'Customer with '.' '.$params['customerEmail'].' '.
                    'email address id already present in payee list';
                } else {
                    $output = $this->addPayeeToCustomer($params, $customer);

                    if ($output) {
                        $message = 'Payee'.' '.$params['customerEmail'].' '.'is added in your list';
                    }
                }
            } else {
                $message = 'No customer found with email address '.' '.$params['customerEmail'];
            }
        } else {
            $message ='Data is not validate';
        }
        return $message;
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
            ->addFieldToFilter('customer_id', $params['customerId'])
            ->addFieldToFilter('payee_customer_id', $customer->getEntityId())
            ->addFieldToFilter('website_id', $customer->getWebsiteId());
        if ($payeeModel->getSize()) {
            return true;
        }
        return false;
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
                case 'customerId':
                    if ($value == '') {
                        $error = 1;
                    }
                    break;
                case 'customerEmail':
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
        $responseMessage=[];
        $payeeModel = $this->walletPayee->create();
        $configStatus = $this->walletHelper->getPayeeStatus();
        if (!$configStatus) {
            $status = 1;
        } else {
            $status = 0;
        }
        $payeeModel->setCustomerId($params['customerId'])
            ->setNickName($params['nickName'])
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
        return true;
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
