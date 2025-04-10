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
 * CheckoutAddressFormData resolver
 */
class CheckoutAddressFormData extends AbstractCheckout implements ResolverInterface
{
    /**
     * IsGuest variable
     *
     * @var boolean
     */
    protected $isGuest;

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
            $this->customer = $this->customerFactory->create();
            if ($this->customerId != 0) {
                $this->customer = $this->customerFactory->create()->load($this->customerId);
                $this->returnArray["lastName"] = $this->customer->getLastname();
                $this->returnArray["firstName"] = $this->customer->getFirstname();
                if ($this->customer->getDefaultBilling() == $this->addressId) {
                    $this->returnArray["addressData"]["isDefaultBilling"] = true;
                } else {
                    $returnArray["addressData"]["isDefaultBilling"] = false;
                }
                if ($this->customer->getDefaultShipping() == $this->addressId) {
                    $this->returnArray["addressData"]["isDefualtShipping"] = true;
                } else {
                    $this->returnArray["addressData"]["isDefualtShipping"] = false;
                }
                $additionalAddress = $this->customer->getAdditionalAddresses();
                foreach ($additionalAddress as $eachAdditionalAddress) {
                    if ($eachAdditionalAddress instanceof \Magento\Framework\DataObject) {
                        $eachAdditionalAddressArray = [];
                        $eachAdditionalAddressArray["value"] = preg_replace(
                            "/(<br\ ?\/?>)+/",
                            ", ",
                            rtrim(
                                preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace(
                                    "/[\n\r]/",
                                    "<br>",
                                    $this->helperCatalog->stripTags($eachAdditionalAddress->format("html"))
                                )),
                                "<br>"
                            )
                        );
                        $eachAdditionalAddressArray["id"] = $eachAdditionalAddress->getId();
                        $this->returnArray["address"][] = $eachAdditionalAddressArray;
                    }
                }
            } else {
                $this->returnArray["isGuest"] = true;
            }

            $standardAttributes = [
                "isDefaultBilling",
                "isDefualtShipping",
                "entity_id",
                "increment_id",
                "parent_id",
                "created_at",
                "updated_at",
                "is_active",
                "city",
                "company",
                "fax",
                "country_id",
                "firstname",
                "lastname",
                "middlename",
                "postcode",
                "prifix",
                "region",
                "region_id",
                "street",
                "suffix",
                "telephone",
                "vat_id",
                "vat_is_valid",
                "vat_request_date",
                "vat_request_id",
                "var_request_success"
            ];

            if ($this->addressId != 0) {
                $address = $this->customerAddress->load($this->addressId);
                $addressData = $address->getData();
                foreach ($addressData as $key => $addata) {
                    if ($addata != "") {
                        if (in_array($key, $standardAttributes)) {
                            $this->returnArray["addressData"][$key] = $addata;
                        } else {
                            // If it's not a standard attribute, add it to custom_attributes
                            $this->returnArray["addressData"]["custom_attributes"][] = [
                                'attribute_code' => $key,
                                'value' => $addata
                            ];
                        }
                    } else {
                        $this->returnArray["addressData"][$key] = "";
                    }
                }
                $this->returnArray["addressData"]["street"] = $address->getStreet();
                
            } else {
                $this->returnArray["addressData"] = new \stdClass();
            }
            $this->returnArray["countryData"] = $this->helper->getAddressCountryData();
            $this->returnArray["defaultCountry"] = $this->helper->getConfigData("general/country/default");
            $this->returnArray["streetLineCount"] = $this->addressHelper->getStreetLines();
            $extraAddressData = $this->helper->getAddressFormExtraData($this->customer);
            foreach ($extraAddressData as $key => $value) {
                $this->returnArray[$key] = $value;
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
     * Function to verify request
     *
     * @return void|json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->isGuest = $this->wholeData["isGuest"] ?? false;
            $this->addressId = $this->wholeData["addressId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
