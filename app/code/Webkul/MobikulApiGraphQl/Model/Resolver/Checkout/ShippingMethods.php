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
 * ShippingMethods resolver
 */
class ShippingMethods extends AbstractCheckout implements ResolverInterface
{
    /**
     * ShippingData variable
     *
     * @var mixed
     */
    protected $shippingData;
    
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
            $store = $this->store;
            $baseCurrency = $store->getBaseCurrencyCode();
            $currency = $this->wholeData["currency"] ?? $baseCurrency;
            $store->setCurrentCurrencyCode($currency);
            $quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $quote = $this->helper->getCustomerQuote($this->customerId);
                $this->quoteId = $quote->getEntityId();
            }
            if ($this->quoteId != 0) {
                $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
            }
            if ($quote->isVirtual()) {
                $totals = $quote->getBillingAddress()->getTotals();
            } else {
                $totals = $quote->getShippingAddress()->getTotals();
            }
            if (isset($totals["grand_total"])) {
                $grandtotal = $totals["grand_total"];
                $this->returnArray["cartTotal"] = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($grandtotal->getValue())
                );
            } else {
                $this->returnArray["cartTotal"] = 0;
            }
            if ($quote->getItemsQty()*1 == 0) {
                $this->returnArray["message"] = __("Sorry Something went wrong !!");
                return $this->returnArray;
            } else {
                $this->returnArray["cartCount"] = (int) $quote->getItemsQty()*1;
            }
            // validate minimum amount check ////////////////////////////////////////
            $isCheckoutAllowed = $quote->validateMinimumAmount();
            if (!$isCheckoutAllowed) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($this->helper->getConfigData("sales/minimum_order/description"))
                );
            }
            $useForShipping = 0;
            if (!empty($this->shippingData) &&
                (
                    isset($this->shippingData["addressId"]) &&
                    ($this->shippingData["addressId"] > 0 || !empty($this->shippingData["newAddress"]))
                )
            ) {
                $saveInAddressBook = 0;
                $this->getShippingMethods($quote);
            } else {
                if ($this->quoteId != "") {
                    $shippingAddressInterface = $this->addressInterface->setCountryId(
                        $quote->getShippingAddress()->getCountry()
                    )
                        ->setPostcode(null)
                        ->setRegionId(null);
                    $availableMethods = $this->shippingMethodManagement
                        ->estimateByExtendedAddress($this->quoteId, $shippingAddressInterface);
                    $isSelected = false;
                    if (!is_array($availableMethods) && $availableMethods->getSize() == 1) {
                        $isSelected = true;
                    }
                    foreach ($availableMethods as $eachMethod) {
                        $oneShipping = [];
                        $oneMethod["isSelected"] = $isSelected;
                        $oneMethod["code"] = $eachMethod->getCarrierCode();
                        $oneMethod["label"] = $eachMethod->getMethodTitle();
                        $oneMethod["price"] = $this->helperCatalog->stripTags(
                            $this->priceHelper->currency((float)$eachMethod->getAmount())
                        );
                        $oneShipping["title"] = $eachMethod->getCarrierTitle();
                        $oneShipping["method"][] = $oneMethod;
                        $this->returnArray["shippingMethods"][] = $oneShipping;
                    }
                    $totals = [];
                    $this->returnArray["success"] = true;
                    return $this->returnArray;
                } else {
                    $this->returnArray["message"] = __("Invalid Quote Id");
                    return $this->returnArray;
                }
            }
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
     * Function to get shipping methods from shipping Data
     *
     * @param \Magento\Quote\Model\Quote $quote quote
     *
     * @return void
     */
    public function getShippingMethods($quote)
    {
        if (!$quote->isVirtual()) {
            $shippingData = $this->shippingData;
            if ($shippingData != "") {
                $sameAsBilling = 0;
                $newAddress = [];
                if ($shippingData["newAddress"] != "") {
                    if (!empty($shippingData["newAddress"])) {
                        $newAddress = $shippingData["newAddress"];
                    }
                }
                $addressId = 0;
                if ($shippingData["addressId"] != "") {
                    $addressId = $shippingData["addressId"];
                }
                $saveInAddressBook = 0;
                if (isset($shippingData["newAddress"]["saveInAddressBook"]) &&
                    $shippingData["newAddress"]["saveInAddressBook"] != ""
                ) {
                    $saveInAddressBook = $shippingData["newAddress"]["saveInAddressBook"];
                }
                $address = $quote->getShippingAddress();
                $addressForm = $this->customerForm;
                $addressForm->setFormCode("customer_address_edit")->setEntityType("customer_address");
                if ($addressId > 0) {
                    $customerAddress = $this->customerAddress->load($addressId)->getDataModel();
                    if ($customerAddress->getId()) {
                        if ($customerAddress->getCustomerId() != $quote->getCustomerId()) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __("Customer Address is not valid.")
                            );
                        }
                        $address->importCustomerAddressData($customerAddress)->setSaveInAddressBook(0);
                        $addressForm->setEntity($address);
                    }
                } else {
                    $addressForm->setEntity($address);
                    $addressData = [
                        "fax" => $newAddress["fax"],
                        "city" => $newAddress["city"],
                        "region" => $newAddress["region"],
                        "prefix" => $newAddress["prefix"] ?? "",
                        "suffix" => $newAddress["suffix"] ?? "",
                        "street" => $newAddress["street"],
                        "company" => $newAddress["company"],
                        "lastname" => $newAddress["lastName"],
                        "postcode" => $newAddress["postcode"],
                        "region_id" => $newAddress["region_id"],
                        "firstname" => $newAddress["firstName"],
                        "telephone" => $newAddress["telephone"],
                        "middlename" => $newAddress["middleName"] ?? "",
                        "country_id" => $newAddress["country_id"],
                        "address_title" => ($newAddress["address_title"]) ?? ""
                    ];
                    
                    if (!empty($newAddress["custom_attributes"])) {
                        foreach ($newAddress["custom_attributes"] as $customAttribute) {
                            $attributeCode = $customAttribute['attribute_code'];
                            $value = $customAttribute['value'];
                            $addressData[$attributeCode] = $value;
                        }
                    }

                    $addressForm->compactData($addressData);
                    $address->setCustomerAddressId(null);
                    // Additional form data, not fetched by extractData (as it fetches only attributes) /////////
                    $address->setSaveInAddressBook($saveInAddressBook);
                    $address->setSameAsBilling($sameAsBilling);
                }
                $address->setCollectShippingRates(true);
                if (($validateRes = $address->validate()) !== true) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __(implode(", ", $validateRes))
                    );
                }
                if (($validateRes = $address->validate()) !== true) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __(implode(", ", $validateRes))
                    );
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Invalid Shipping data.")
                );
            }
            $quote->collectTotals()->save();
            $quote->getShippingAddress()->collectShippingRates()->save();
            $shippingRateGroups = $quote->getShippingAddress()->getGroupedAllShippingRates();
            $isSelected = false;
            if (count($shippingRateGroups) == 1) {
                $isSelected = true;
            }
            foreach ($shippingRateGroups as $code => $rates) {
                $oneShipping = [];
                $oneShipping["isSelected"] = $isSelected;
                $oneShipping["title"] = $this->helperCatalog->stripTags(
                    $this->helper->getConfigData("carriers/".$code."/title")
                );
                foreach ($rates as $rate) {
                    $oneMethod = [];
                    if ($rate->getErrorMessage()) {
                        $oneMethod["error"] = $rate->getErrorMessage();
                    }
                    $oneMethod["code"] = $rate->getCode();
                    $oneMethod["label"] = $rate->getMethodTitle();
                    $oneMethod["price"] = $this->helperCatalog->stripTags(
                        $this->priceHelper->currency((float)$rate->getPrice())
                    );
                    $oneShipping["method"][] = $oneMethod;
                }
                $this->returnArray["shippingMethods"][] = $oneShipping;
            }
        }
    }

    /**
     * Function to verify Request
     *
     * @return void|json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->shippingData = $this->wholeData["shippingData"] ?? [];
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
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
}
