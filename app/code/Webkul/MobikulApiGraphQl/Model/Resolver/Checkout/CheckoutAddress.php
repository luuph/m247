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

/**
 * CheckoutAddress resolver
 */
class CheckoutAddress extends AbstractCheckout implements ResolverInterface
{
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
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $addressArray = [];
            $addressIds = [];
            $quote = null;
    
            if ($this->customerId != 0) { // Handle registered customer
                $customer = $this->customerFactory->create()->load($this->customerId);
                $addressArray = [];
            
                // Get all customer addresses
                $allAddresses = $customer->getAddresses();
                foreach ($allAddresses as $address) {
                    if ($address instanceof \Magento\Framework\DataObject) {
                        $addressArray[] = $this->cleanAddressData([], $address);
                    }
                }
            
                $quote = $this->helper->getCustomerQuote($this->customerId);
                $this->returnArray["firstName"] = $customer->getFirstname();
                $this->returnArray["lastName"] = $customer->getLastname();
                $this->returnArray["middleName"] = $customer->getMiddlename() ?? "";
                $this->returnArray["prefixValue"] = $customer->getPrefix() ?? "";
                $this->returnArray["suffixValue"] = $customer->getSuffix() ?? "";
                $this->returnArray["address"] = $addressArray;
            }
            
            
          

            if ($this->customerId == 0 && $this->quoteId != 0) {
                $quote = $this->helper->getGuestQuote($this->quoteId);
                if (!$quote || !$quote->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Quote not found for the provided quote ID.")
                    );
                }
            
                $shippingAddress = $quote->getShippingAddress();
                
                if ($shippingAddress && $shippingAddress->getId()) {
                    // Decode custom attributes JSON if available
                    $customAttributesJson = $shippingAddress->getData('customer_address_attribute');
                    $customAttributes = [];
                    if ($customAttributesJson) {
                        $customAttributes = json_decode($customAttributesJson, true) ?: [];
                    }
            
                    // Check if shipping address has actual data
                    $hasData = !empty($shippingAddress->getCity()) || !empty($shippingAddress->getCountryId());
            
                    if ($hasData) {
                        // Map custom attributes to response
                        $addressArray[] = [
                            'value' => $shippingAddress->format("html") ?: '',
                            'id' => $shippingAddress->getId(),
                            'address_title' => $customAttributes['address_title']['value'] ?? '',
                            'apt_number' => $customAttributes['apt_number']['value'] ?? '',
                            'building_name' => $customAttributes['building_name']['value'] ?? '',
                            'avenue' => $customAttributes['avenue']['value'] ?? '',
                            'floor' => $customAttributes['floor']['value'] ?? '',
                            'city' => $shippingAddress->getCity() ?: '',
                            'city_id' => $shippingAddress->getData('city_id') ?? '',
                            'country_id' => $shippingAddress->getCountryId() ?: '',
                            'firstname' => $shippingAddress->getFirstname() ?: '',
                            'lastname' => $shippingAddress->getLastname() ?: '',
                            'region' => $shippingAddress->getRegion() ?: '',
                            'region_id' => $shippingAddress->getRegionId() ?: '',
                            'street' => $shippingAddress->getStreet() ? implode(', ', $shippingAddress->getStreet()) : '',
                            'telephone' => $shippingAddress->getTelephone() ?: '',
                        ];
                    } else {
                        // If no address data exists, return an empty array
                        $addressArray = [];
                    }
                } else {
                    // If no shipping address exists, return an empty array
                    $addressArray = [];
                }
            }
            
            
            
    
            $this->returnArray["address"] = $addressArray;
    
            if (!$quote || $quote->getItemsQty() * 1 == 0) {
                $this->returnArray["message"] = __("Your cart is empty.");
                return $this->returnArray;
            }
    
            $this->returnArray["cartCount"] = (int) $quote->getItemsQty() * 1;
            $this->returnArray["isVirtual"] = $quote->isVirtual();
            $this->returnArray["streetLineCount"] = $this->addressHelper->getStreetLines();
            $this->returnArray["defaultCountry"] = $this->helper->getConfigData("general/country/default");
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
    
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }
    
    
    /**
     * Function to verify request
     *
     * @return void|json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
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






    private function cleanAddressData($baseAddress, $address)
{
    return array_filter([
        'value' => $baseAddress['value'] ?? $address->format("html"),
        'id' => $baseAddress['id'] ?? $address->getId(),
        'address_title' => $address->getAddressTitle(),
        'apt_number' => $address->getAptNumber(),
        'building_name' => $address->getBuildingName(),
        'city' => $address->getCity(),
        'city_id' => $address->getCityId(),
        'country_id' => $address->getCountryId(),
        'firstname' => $address->getFirstname(),
        'floor' => $address->getFloor(),
        'lastname' => $address->getLastname(),
        'region' => $address->getRegion(),
        'region_id' => $address->getRegionId(),
        'street' => implode(', ', $address->getStreet()),
        'telephone' => $address->getTelephone(),
        'avenue' => $address->getAvenue(),
    ], function ($value) {
        return !is_null($value) && $value !== ''; // Exclude null or empty values
    });
}

}

