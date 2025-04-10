<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Customer;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SaveAddress extends AbstractCustomer implements ResolverInterface
{
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

            if (empty($this->customerId)) {
                // Handle guest user
                return $this->saveGuestAddress();
            } else {
                // Handle registered customer
                return $this->saveCustomerAddress();
            }
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * Save address for a registered customer
     *
     * @return array
     */
    private function saveCustomerAddress()
    {
        $this->addressData["lastname"] = $this->addressData["lastName"];
        $this->addressData["firstname"] = $this->addressData["firstName"];
        $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
        $addressDataArr = [];

        foreach ($this->addressData as $key => $addressValue) {
            $addressDataArr[$key] = $addressValue;
        }

        // Handle custom attributes dynamically
        if (isset($this->addressData['custom_attributes']) && is_array($this->addressData['custom_attributes'])) {
            foreach ($this->addressData['custom_attributes'] as $customAttribute) {
                if (isset($customAttribute['attribute_code']) && isset($customAttribute['value'])) {
                    $addressDataArr[$customAttribute['attribute_code']] = $customAttribute['value'];
                }
            }
        }

        $customer = $this->customerFactory->create()->load($this->customerId);
        $customerSession = $this->customerSession->setCustomer($customer);
        $address = $this->customerAddress;

        if ($this->addressId != 0) {
            $existsAddress = $customer->getAddressById($this->addressId);
            if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                $address->setId($existsAddress->getId());
            }
        }

        $errors = [];
        $addressForm = $this->customerForm;
        $addressForm->setFormCode("customer_address_edit")->setEntity($address);
        $addressErrors = $addressForm->validateData($addressDataArr);

        if ($addressErrors !== true) {
            $errors = $addressErrors;
        }

        $addressForm->compactData($addressDataArr);
        $address->setCustomerId($this->customerId)
            ->setIsDefaultBilling($addressDataArr["default_billing"])
            ->setIsDefaultShipping($addressDataArr["default_shipping"]);
        $addressErrors = $address->validate();
        $address->save();

        $this->returnArray["message"] = __("The address has been saved.");
        $this->returnArray["success"] = true;
        $this->emulate->stopEnvironmentEmulation($environment);

        return $this->returnArray;
    }

    /**
     * Save address for a guest user
     *
     * @return array
     */
    private function saveGuestAddress()
    {
        $quoteId = $this->addressData['quoteId'] ?? null;
    
        if (!$quoteId) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Quote ID is required for guest users.")
            );
        }
    
        // Retrieve the guest quote using the helper
        $quote = $this->helper->getGuestQuote($quoteId);
        if (!$quote || !$quote->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Unable to retrieve the quote for the guest user.")
            );
        }
    
        $addressData = [
            'firstname' => $this->addressData["firstName"],
            'lastname' => $this->addressData["lastName"],
            'street' => $this->addressData["street"],
            'city' => $this->addressData["city"],
            'country_id' => $this->addressData["country_id"],
            'region' => $this->addressData["region"],
            'region_id' => $this->addressData["region_id"],
            'postcode' => $this->addressData["postcode"],
            'telephone' => $this->addressData["telephone"],
            'fax' => $this->addressData["fax"],
            'save_in_address_book' => 0,
        ];
    
        // Add custom attributes if available
        if (isset($this->addressData['custom_attributes'])) {
            foreach ($this->addressData['custom_attributes'] as $customAttribute) {
                if (isset($customAttribute['attribute_code']) && isset($customAttribute['value'])) {
                    $addressData[$customAttribute['attribute_code']] = $customAttribute['value'];
                }
            }
        }
    
        // Assign the address to the shipping address of the quote
        $quote->getShippingAddress()
            ->addData($addressData)
            ->setSaveInAddressBook(false)
            ->save();
    
        // Collect totals and save the quote
        $quote->collectTotals()->save();
    
        $this->returnArray["message"] = __("The address has been saved for the guest user.");
        $this->returnArray["success"] = true;
    
        return $this->returnArray;
    }
    

    /**
     * Verify Request function to verify the request
     *
     * @return void|Json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->addressId = $this->wholeData["addressId"] ?? 0;
            $this->addressData = $this->wholeData["addressData"] ?? [];
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);

            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
