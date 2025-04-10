<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulwalletGraphQl
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c)Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MobikulwalletGraphQl\Model\Resolver\Index;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Addpayee extends AbstractWallet implements ResolverInterface
{
    protected $nickName;

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->wholeData = $args;
        try {
            $this->verifyRequest();
            if (
                $this->nickName && $this->customerEmail
                && !preg_match('#<script(.*?)>(.*?)</script>#is', $this->nickName)
            ) {
                $customer = $this->_customerFactory->create();
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
                if (isset($websiteId)) {
                    $customer->setWebsiteId($websiteId);
                }
                $this->returnArray["customerId"] = $this->customerId;
                $wholeData['customer_id'] = $this->customerId;
                $wholeData['nickname'] = $this->nickName;
                $customer->loadByEmail($this->customerEmail);
                if ($customer && $customer->getId()) {
                    if ($customer->getId() == $this->customerId) {
                        $result['error_msg'] = __("You can not add yourself in your payee list.");
                        $result['error'] = 1;
                    } elseif ($this->alreadyAddedInPayee($wholeData, $customer)) {
                        $result['error_msg'] = __("Customer with %1 email address id already present in payee list", $this->customerEmail);
                        $result['error'] = 1;
                    } else {
                        $result = $this->addPayeeToCustomer($wholeData, $customer);
                    }
                } else {
                    $result['error_msg'] = __(
                        "No customer found with email address %1",
                        $this->customerEmail
                    );
                    $result['error'] = 1;
                }
                if ($result['error'] == 1) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($result['error_msg'])
                    );
                } else {
                    $this->returnArray["success"] = true;
                    $this->returnArray["message"] = __('Payee is added in your list');
                }
            }
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            return $this->returnArray;
        }
    }

    public function addPayeeToCustomer($params, $customer)
    {
        $message = '';
        $payeeModel = $this->walletPayee->create();
        $configStatus = $this->_walletHelper->getPayeeStatus();
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
        $payeeApprovalRequired = $this->scopeConfig->getValue('walletsystem/transfer_settings/payeestatus', $storeScope);
        if ($payeeApprovalRequired) {
            $this->setNotificationMessageForAdmin();
        }
        if ($payeeApprovalRequired) {
            $displayCustomMessage = $this->scopeConfig->getValue('walletsystem/transfer_settings/show_payee_message', $storeScope);
            if ($displayCustomMessage) {
                $message = __($this->scopeConfig->getValue('walletsystem/transfer_settings/show_payee_message_content', $storeScope));
            }
        }
        $result = [
            'error' => 0,
            'success_msg' => ($message) ? $message : __('Payee is added in your list')
        ];
        return $result;
    }
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

    public function setNotificationMessageForAdmin()
    {
        $notificationModel = $this->_walletNotification->getCollection();
        if (!$notificationModel->getSize()) {
            $this->_walletNotification->setPayeeCounter(1);
            $this->_walletNotification->save();
        } else {
            foreach ($notificationModel->getItems() as $notification) {
                $notification->setPayeeCounter($notification->getPayeeCounter() + 1);
            }
        }
        $notificationModel->save();
    }

    /**
     * Function verify Request to authenticate the request
     *
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    public function verifyRequest()
    {
        if ($this->wholeData) {
            $this->storeId       = $this->wholeData["storeId"];
            $this->nickName      = $this->wholeData["nickName"];
            $this->customerEmail = $this->wholeData["customerEmail"] ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->customerId    = $this->_helper->getCustomerByToken($this->customerToken) ?? 0;
            if ($this->customerId == 0) {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist, so you need to logout.")
                );
            } elseif ($this->customerId != 0) {
                $this->customer = $this->_customerFactory->create()->load($this->customerId);
                $this->_customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException("Invalid Request");
        }
    }
}
