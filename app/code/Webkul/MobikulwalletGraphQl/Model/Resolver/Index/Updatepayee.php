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

class Updatepayee extends AbstractWallet implements ResolverInterface
{
    protected $id;
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
            if ($this->id != 0 && $this->nickName != "") {
                $payeeModel = $this->walletPayee->create()->load($this->id);
                $configStatus = $this->_walletHelper->getPayeeStatus();
                if (!$configStatus) {
                    $status = $payeeModel::PAYEE_STATUS_ENABLE;
                } else {
                    $status = $payeeModel::PAYEE_STATUS_DISABLE;
                }
                $payeeModel->setNickName($this->nickName)->save();
                $this->returnArray["success"] = true;
                $this->returnArray["message"]      = __("Payee is updated");
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Payee not Found, Please try again later")
                );
            }
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            return $this->returnArray;
        }
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
            $this->storeId       = $this->wholeData["storeId"] ?? 1;
            $this->id            = $this->wholeData["id"] ?? 0;
            $this->nickName      = $this->wholeData["nickName"] ?? '';
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
