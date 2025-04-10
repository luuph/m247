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

class Customerbankaccountdetails extends AbstractWallet implements ResolverInterface
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
                $collectionData = $this->accountResourceModel->create()
                ->addFieldToFilter('customer_id', $this->customerId)
                ->addFieldToFilter('status', ['neq' => 0])
                ->setOrder('entity_id','DESC');

                $accountDetails = [];
                foreach ($collectionData as $record) {
                    $id = $record->getId();
                    $email = $this->_customerFactory->create()->load($record->getCustomerId())->getEmail();
                    $customerName = $this->_customerFactory->create()->load($record->getCustomerId())->getName();
                    $accountDetails[$id]['id'] = $record->getId();
                    $accountDetails[$id]['CustomerName'] = $customerName;
                    $accountDetails[$id]['CustomerEmail'] =$email;
                    $accountDetails[$id]['acholderName'] = $record->getHoldername();
                    $accountDetails[$id]['acNumber'] = $record->getAccountno();
                    $accountDetails[$id]['bankName'] =$record->getBankname();
                    $accountDetails[$id]['bankCode'] =$record->getBankcode();
                    $accountDetails[$id]['additionalInformation'] =$record->getAdditional();
                    $accountDetails[$id]['RequestForDelete'] =$record->getRequestToDelete() == 0 ? 'No': 'Yes' ;
                }
                $this->returnArray["success"] = true;
                if(empty($accountDetails)){
                    $this->returnArray["success"] = false;
                    $this->returnArray["message"] = __("No records found.");
                } else {
                    $this->returnArray["accountDetails"] = $accountDetails;
                }
            } else {
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
