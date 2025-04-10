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
use Magento\Framework\App\ObjectManager;
use Bss\GiftCard\Model\ResourceModel\GiftCard\QuoteFactory;
use Mirasvit\Rewards\Model\PurchaseFactory;

/**
 * ReviewAndPaymant resolver
 */
class ReviewAndPayment extends AbstractCheckout implements ResolverInterface
{
    /**
     * Currency variable
     *
     * @var string
     */
    protected $currency;

    /**
     * ShippingMethod variable
     *
     * @var mixed
     */
    protected $shippingMethod;

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
            $this->customerSession->setId($this->customerId);
            $environment = $this->emulate->startEnvironmentEmulation(
                $this->storeId,
                \Magento\Framework\App\Area::AREA_FRONTEND,
                true
            );
            $store = $this->store;
            $baseCurrency = $store->getBaseCurrencyCode();
            $this->currency = $this->wholeData["currency"] ?? $baseCurrency;
            $store->setCurrentCurrencyCode($this->currency);
            $quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $quote = $this->helper->getCustomerQuote($this->customerId);
            }
            if ($this->quoteId != 0) {
                $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
            }
            
            //get the Gift Code applied on that quote
            $objectManager = ObjectManager::getInstance();
            $giftCardQuoteFactory = $objectManager->create(QuoteFactory::class);
            $giftCardQuote = $giftCardQuoteFactory->create();
            $giftCardCodes = $giftCardQuote->getGiftCardCode($quote);
            if (!empty($giftCardCodes)) {
                $this->returnArray['giftCode'] = $giftCardCodes['0']['giftcard_code'];
                $this->returnArray['giftCodeId'] = $giftCardCodes['0']['id'];
            }else {
                $this->returnArray['giftCode'] = "";
            }

            //get Loyalty Spend Points and Spend Amount
            $objectManager = ObjectManager::getInstance();
            $purchaseFactory = $objectManager->create(PurchaseFactory::class);
            $purchase = $purchaseFactory->create();
            $purchase->load($quote->getId(), 'quote_id');
            if ($purchase->getId()) {
                $this->returnArray['spendPoints'] = $purchase->getSpendPoints();
                $this->returnArray['spendAmount'] = $purchase->getSpendAmount();
            }

            if ($quote->getItemsQty()*1 == 0) {
                $this->returnArray["message"] = __("Sorry Something went wrong !!");
                return $this->returnArray;
            } else {
                $this->returnArray["cartCount"] = (int) $quote->getItemsQty()*1;
            }
            $this->returnArray["quoteId"] = $quote->getId();
            // validate minimum amount check ////////////////////////////////////////
            $isCheckoutAllowed = $quote->validateMinimumAmount();
            if (!$isCheckoutAllowed) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($this->helper->getConfigData("sales/minimum_order/description"))
                );
            }
            if ($this->shippingMethod != "") {
                $shippingMethod = $this->shippingMethod;
                $rate = $quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
                if (!$rate) {
                    $returnArray["message"] = __("Invalid shipping method.");
                    return $returnArray;
                }
                $quote->getShippingAddress()->setShippingMethod($this->shippingMethod);
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->collectTotals()->save();
            }
            $orderReviewData = $this->getOrderReviewData($quote);
            $this->getPaymentMethods($quote);
            $this->returnArray["success"] = true;
            $this->returnArray['couponCode'] = $quote->getCouponCode()??'';
            $this->returnArray["currencyCode"] = $this->storeManager->getStore()->getCurrentCurrencyCode();
            $this->returnArray["orderReviewData"] = $orderReviewData;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * Function to veriy the Request
     *
     * @return void|object
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->shippingMethod = $this->wholeData["shippingMethod"] ?? "";
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
    
    /**
     * Function to populate return array with payment methods
     *
     * @param \Magento\Framework\DataObject $quote quote
     *
     * @return void
     */
    protected function getPaymentMethods($quote)
    {
        $paymentDetails = $this->paymentDetails
            ->setPaymentMethods(
                $this->paymentMethodInterface->getList($quote->getId())
            )->getData();
        $allowedMethods = $this->helper->getConfigData("mobikul/mobikulpayment/selectedmethods");
        $allowedMethods = explode(',', $allowedMethods ?? "");
        foreach ($paymentDetails["payment_methods"] as $method) {
            if (in_array($method->getCode(), $allowedMethods)) {
                $oneMethod = [];
                $oneMethod["code"] = $method->getCode();
                $oneMethod["title"] = $method->getTitle();
                $oneMethod["extraInformation"] = "";
                if (in_array($method->getCode(), ["paypal_standard", "paypal_express"])) {
                    $oneMethod["extraInformation"] = __("You will be redirected to the PayPal website.");
                    $config = $this->paypalConfig->setMethod($method->getCode());
                    $locale = $this->localeResolver;
                    $oneMethod["title"] = "";
                    $oneMethod["link"] = $config->getPaymentMarkWhatIsPaypalUrl($locale);
                    $oneMethod["imageUrl"] = $config->getPaymentMarkImageUrl($locale->getDefaultLocale());
                } elseif (in_array($method->getCode(), ["paypal_express_bml"])) {
                    $oneMethod["extraInformation"] = __("You will be redirected to the PayPal website.");
                    $oneMethod["title"] = "";
                    $oneMethod["link"] = "https://www.securecheckout.billmelater.com/paycapture-content/fetch".
                    "?hash=AU826TU8&content=/bmlweb/ppwpsiw.html";
                    $oneMethod["imageUrl"] = "https://www.paypalobjects.com/webstatic/en_US/i/buttons/".
                    "ppc-acceptance-medium.png";
                } elseif ($method->getCode() == "checkmo") {
                    if ($method->getPayableTo()) {
                        $extraInformationPrefix = __("Make Check payable to:");
                    } else {
                        $extraInformationPrefix = __("Send Check to:");
                    }
                    $extraInformation = $this->helper->getConfigData("payment/".$method->getCode()."/mailing_address");
                    if ($extraInformation == "") {
                        $extraInformation = __(" xxxxxxx");
                    }
                    $oneMethod["extraInformation"] = $extraInformationPrefix.$extraInformation;
                } elseif ($method->getCode() == "banktransfer") {
                    $extraInformation = $this->helper->getConfigData("payment/".$method->getCode()."/instructions");
                    if ($extraInformation == "") {
                        $extraInformation = __("Bank Details are xxxxxxx");
                    }
                    $oneMethod["extraInformation"] = $extraInformation;
                } elseif ($method->getCode() == "cashondelivery") {
                    $extraInformation = $this->helper->getConfigData("payment/".$method->getCode()."/instructions");
                    if ($extraInformation == "") {
                        $extraInformation = __("Pay at the time of delivery");
                    }
                    $oneMethod["extraInformation"] = $extraInformation;
                } elseif (in_array($method->getCode(), ["webkul_stripe", "authorizenet"])) {
                    $allowedCc = [];
                    $allowedCcTypesString = $method->getConfigData("cctypes");
                    $allowedCcTypes = explode(",", $allowedCcTypesString ?? "");
                    $ccTypes = $this->ccType->toOptionArray();
                    $types = [];
                    foreach ($ccTypes as $data) {
                        if (isset($data["value"]) && isset($data["label"])) {
                            $types[$data["value"]] = $data["label"];
                        }
                    }
                    foreach ($allowedCcTypes as $value) {
                        $eachAllowedCc = [];
                        $eachAllowedCc["code"] = $value;
                        $eachAllowedCc["name"] = $types[$value];
                        $allowedCc[] = $eachAllowedCc;
                    }
                    $extraInformation = $allowedCc;
                    $oneMethod["extraInformation"] = $extraInformation;
                }
                $this->returnArray["paymentMethods"][] = $oneMethod;
            }
        }
    }

    /**
     * ArrayMerge function
     *
     * @param array $arr1
     * @param array $arr2
     * @return void
     */
    public function arrayMerge($arr1, $arr2)
    {
        return array_merge($arr1, $arr2);
    }

    /**
     * Function to get order Review Data
     *
     * @param \Magento\Quote\Model\Quote $quote quote
     *
     * @return array
     */
    public function getOrderReviewData($quote)
    {
        $orderReviewData = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $eachItem = [];
            $eachItem["productName"] = $this->helperCatalog->stripTags($item->getName());
            $customoptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            $result = [];
            if ($customoptions) {
                if (isset($customoptions["options"])) {
                    $result = $this->arrayMerge($result, $customoptions["options"]);
                }
                if (isset($customoptions["additional_options"])) {
                    $result = $this->arrayMerge($result, $customoptions["additional_options"]);
                }
                if (isset($customoptions["attributes_info"])) {
                    $result = $this->arrayMerge($result, $customoptions["attributes_info"]);
                }
            }
            if ($result) {
                foreach ($result as $option) {
                    $eachOption = [];
                    $eachOption["optionId"] = $option["option_id"];
                    $eachOption["label"] = htmlspecialchars_decode($option["label"]);
                    if (is_array($option["value"])) {
                        $eachOption["valueIds"] = $option["option_value"];
                        $eachOption["value"] = $option["value"];
                    } else {
                        $eachOption["valueIds"][] = $this->helperCatalog->stripTags($option["option_value"]);
                        $eachOption["value"][] = $this->helperCatalog->stripTags($option["value"]);
                    }
                    $eachItem["options"][] = $eachOption;
                }
            }
            $eachItem["qty"] = $item->getQty();
            $eachItem["unformattedOriginalPrice"] =round($this->priceCurrencyInterface->convert(
                $item->getProduct()->getPrice(),
                $this->storeId,
                $this->currency
            ), 2);
            $eachItem["originalPrice"] = $this->helperCatalog->stripTags(
                $this->checkoutHelper->formatPrice(
                    $this->priceCurrencyInterface->convert($item->getProduct()->getPrice())
                ),
                $this->storeId,
                $this->currency
            );
            $eachItem["price"] = $this->helperCatalog->stripTags(
                $this->checkoutHelper->formatPrice($item->getCalculationPrice())
            );
            $eachItem["subTotal"] = $this->helperCatalog->stripTags(
                $this->checkoutHelper->formatPrice($item->getRowTotal())
            );
            $eachItem["thumbnail"] = $this->helperCatalog->getImageUrl(
                $item->getProduct(),
                $this->width/2.5,
                "product_page_image_small"
            );
            $eachItem["dominantColor"] = $this->helper->getDominantColor(
                $this->helper->getDominantColorFilePath(
                    $this->helperCatalog->getImageUrl($item->getProduct(), $this->width/2.5, "product_page_image_small")
                )
            );
            $eachItem["unformattedPrice"] = $item->getCalculationPrice();
            $orderReviewData["items"][]   = $eachItem;
        }

        $address = $quote->getBillingAddress();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $addressRepository = $objectManager->get(\Magento\Customer\Api\AddressRepositoryInterface::class);

        $billingAddressId = $address->getCustomerAddressId()?? 0;
        if ($billingAddressId) {
            $customAddress = $addressRepository->getById($billingAddressId);
            $customAddressAttributes = $customAddress->getCustomAttributes();
            $billingAddressArray[] = [
                'id' => $address->getId(),
                'address_title' => isset($customAddressAttributes['address_title']) ? $customAddressAttributes['address_title']->getValue() : '',
                'apt_number' => isset($customAddressAttributes['apt_number']) ? $customAddressAttributes['apt_number']->getValue() : '',
                'building_name' => isset($customAddressAttributes['building_name']) ? $customAddressAttributes['building_name']->getValue() : '',
                'avenue' => isset($customAddressAttributes['avenue']) ? $customAddressAttributes['avenue']->getValue() : '',
                'city' => $address->getCity(),
                'city_id' => isset($customAddressAttributes['city_id']) ? $customAddressAttributes['city_id']->getValue() : '',
                'country_id' => $address->getCountryId(),
                'firstname' => $address->getFirstname(),
                'floor' => isset($customAddressAttributes['floor']) ? $customAddressAttributes['floor']->getValue() : '',
                'lastname' => $address->getLastname(),
                'region' => $address->getRegion(),
                'region_id' => $address->getRegionId(),
                'street' => implode(', ', $address->getStreet()),
                'telephone' => $address->getTelephone(),
            ];
            $this->returnArray["billingAddressData"] = $billingAddressArray;
        }
 
        if ($address instanceof \Magento\Framework\DataObject) {
            $this->returnArray["billingAddress"] = $address->format("html");
        }

        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $address = $quote->getShippingAddress();
            $shippingAddressArray = [];
        
            // Check if the user is logged in or a guest
            if ($quote->getCustomerId()) {
                // For logged-in customers
                $shippingAddressId = $address->getCustomerAddressId() ?? 0;
                if ($shippingAddressId) {
                    $customShippingAddress = $addressRepository->getById($shippingAddressId);
                    $customShippingAttributes = $customShippingAddress->getCustomAttributes();
        
                    $shippingAddressArray[] = [
                        'id' => $address->getId(),
                        'address_title' => $customShippingAttributes['address_title']->getValue() ?? '',
                        'apt_number' => $customShippingAttributes['apt_number']->getValue() ?? '',
                        'building_name' => $customShippingAttributes['building_name']->getValue() ?? '',
                        'city_id' => $customShippingAttributes['city_id']->getValue() ?? '',
                        'avenue' => $customShippingAttributes['avenue']->getValue() ?? '',
                        'floor' => $customShippingAttributes['floor']->getValue() ?? '',
                        'city' => $address->getCity(),
                        'country_id' => $address->getCountryId(),
                        'firstname' => $address->getFirstname(),
                        'lastname' => $address->getLastname(),
                        'region' => $address->getRegion(),
                        'region_id' => $address->getRegionId(),
                        'street' => implode(', ', $address->getStreet()),
                        'telephone' => $address->getTelephone(),
                    ];
                }
            } else {
                $shippingAddress = $quote->getShippingAddress();
                $customAttributesJson = $shippingAddress->getData('customer_address_attribute');
                $customAttributes = [];
                if ($customAttributesJson) {
                    $customAttributes = json_decode($customAttributesJson, true) ?: [];
                }
        
                // For guest users
                $shippingAddressArray[] = [
                    'id' => $address->getId(),
                    'address_title' => $customAttributes['address_title']['value'] ?? $shippingAddress->getData('address_title') ?? null,
                    'apt_number' => $customAttributes['apt_number']['value'] ?? $shippingAddress->getData('apt_number') ?? null,
                    'building_name' => $customAttributes['building_name']['value'] ?? $shippingAddress->getData('building_name') ?? null,
                    'avenue' => $customAttributes['avenue']['value'] ?? $shippingAddress->getData('avenue') ?? null,
                    'floor' => $customAttributes['floor']['value'] ?? $shippingAddress->getData('floor') ?? null,
                    'city_id' => $customAttributes['city_id']['value'] ?? $shippingAddress->getData('city_id') ?? null,
                    'city' => $address->getCity(),
                    'country_id' => $address->getCountryId(),
                    'firstname' => $address->getFirstname(),
                    'lastname' => $address->getLastname(),
                    'region' => $address->getRegion(),
                    'region_id' => $address->getRegionId(),
                    'street' => implode(', ', $address->getStreet()),
                    'telephone' => $address->getTelephone(),
                ];
            }
            
            $this->returnArray["shippingAddressData"] = $shippingAddressArray;
        
            // Add shipping method description
            $this->returnArray["shippingMethod"] = $quote->getShippingAddress()->getShippingDescription()
                ? $this->helperCatalog->stripTags($quote->getShippingAddress()->getShippingDescription())
                : '';
        
            // If billing address is not set, use shipping address as billing address
            if (!$billingAddressId) {
                $this->returnArray["billingAddress"] = $this->returnArray["shippingAddress"] ?? '';
                $this->returnArray["billingAddressData"] = $this->returnArray["shippingAddressData"] ?? [];
            }
        } else {
            $this->returnArray["message"] = __("Shipping address is not available.");
        }
        

        $totals = [];
        if ($quote->isVirtual()) {
            $totals = $quote->getBillingAddress()->getTotals();
        } else {
            $totals = $quote->getShippingAddress()->getTotals();
        }
        $orderReviewData["cartTotal"] = 0;
        if (isset($totals["subtotal"])) {
            $subtotal = $totals["subtotal"];
            $orderReviewData["totals"][] = [
                "title" => $subtotal->getTitle(),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($subtotal->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($subtotal->getValue())
                ),
                "unformattedValue" => $subtotal->getValue()
            ];
        }
        if (isset($totals["discount"])) {
            $discount = $totals["discount"];
            $orderReviewData["totals"][] = [
                "title" => $discount->getTitle(),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($discount->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($discount->getValue())
                ),
                "unformattedValue" => $discount->getValue()
            ];
        }
        if (isset($totals["tax"])) {
            $tax = $totals["tax"];
            $orderReviewData["totals"][] = [
                "title" => $tax->getTitle(),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($tax->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($tax->getValue())
                ),
                "unformattedValue" => $tax->getValue()
            ];
        }
        if (isset($totals["shipping"]) && $this->shippingMethod != "") {
            $shipping = $totals["shipping"];
            $orderReviewData["totals"][] = [
                "title" => $shipping->getTitle(),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($shipping->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($shipping->getValue())
                ),
                "unformattedValue" => $shipping->getValue()
            ];
        }
        if ($quote->getBssGiftcardAmount()) {
            $value = $quote->getBssGiftcardAmount();
            $value = $value * -1;
            $orderReviewData["totals"][] = [
                "title" => "Gift Discount",
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($value)),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($value)
                ),
                "unformattedValue" => $value
            ];
        }
        if (isset($totals["grand_total"])) {
            $grandtotal = $totals["grand_total"];
            $orderReviewData["totals"][] = [
                // "title" => $grandtotal->getTitle(),
                "title" => __("Order Total"),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($grandtotal->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($grandtotal->getValue())
                ),
                "unformattedValue" => $grandtotal->getValue()
            ];
            $this->returnArray["cartTotal"] = $this->helperCatalog->stripTags(
                $this->checkoutHelper->formatPrice($grandtotal->getValue())
            );
        }

        if (!empty($orderReviewData)) {
            return $orderReviewData;
        } else {
            return new \stdClass();
        }
    }
}
