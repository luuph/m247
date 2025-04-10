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

class Customeraddbankaccountdetails extends AbstractWallet implements ResolverInterface
{
    protected $customerId;
    protected $acholderName;
    protected $acNumber;
    protected $bankName;
    protected $bankCode;
    protected $additionalInformation;
    protected $customerToken;

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
            if ($this->_walletHelper->getWalletenabled()) {
                $accountDetails  = [
                    'customer_id' => $this->customerId,
                    'holdername' =>  $this->acholderName,
                    'accountno'  =>  $this->acNumber,
                    'bankname'   =>  $this->bankName,
                    'bankcode'   =>  $this->bankCode,
                    'additional' =>  $this->additionalInformation
                ];
               
                $this->accountDetails->setData($accountDetails)->save();
                $this->returnArray["success"] = true;
                $this->returnArray['message'] = 'Account Information Saved Successfully';
    
            } else {
                $this->returnArray["success"] = false;
                $this->returnArray["message"] = __("Payment method is not enabled.");
            }
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["success"] = false;
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
            $this->acholderName          = $this->wholeData["acholderName"];
            $this->acNumber              = $this->wholeData["acNumber"];
            $this->bankName              = $this->wholeData["bankName"];
            $this->bankCode              = $this->wholeData["bankCode"];
            $this->additionalInformation = $this->wholeData["additionalInformation"];
            $this->customerToken         = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->_helper->getCustomerByToken($this->customerToken) ?? 0;
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
