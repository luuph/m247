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

/**
 * AddressBookData resolver
 */
class AddressBookData extends AbstractCustomer implements ResolverInterface
{
    /**
     * ForDashboard variable
     *
     * @var mixed
     */
    protected $forDashboard;

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
        $this->returnArray["billingAddress"]["id"] = 0;
        $this->returnArray["shippingAddress"]["id"] = 0;
        $this->returnArray["billingAddress"]["value"] = __(
            "You have no default billing address in your address book."
        )->__toString();
        $this->returnArray["shippingAddress"]["value"] = __(
            "You have no default shipping address in your address book."
        )->__toString();
        $this->returnArray["billingAddress"]["addressTitle"] = "";
        $this->returnArray["shippingAddress"]["addressTitle"] = "";
        try {
            $this->verifyRequest();
            $addressCount = 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customer = $this->customerFactory->create()->load($this->customerId);
            $address = $this->customer->getPrimaryBillingAddress();
            if ($address) {
                $this->returnArray["billingAddress"]["value"] = $address->format("html");
                $this->returnArray["billingAddress"]["id"] = $address->getId();
                $this->returnArray["billingAddress"]["addressTitle"] = $address->getAddressTitle();
                $addressCount++;
            }
            $address = $this->customer->getPrimaryShippingAddress();
            if ($address) {
                $this->returnArray["shippingAddress"]["value"] = $address->format("html");
                $this->returnArray["shippingAddress"]["id"] = $address->getId();
                $this->returnArray["shippingAddress"]["addressTitle"] = $address->getAddressTitle();
                $addressCount++;
            }

            if (!$this->forDashboard) {
                $additionalAddress = $this->customer->getAdditionalAddresses();
                foreach ($additionalAddress as $eachAdditionalAddress) {
                    $eachAdditionalAddressArray = [];
                    if ($eachAdditionalAddress) {
                        $eachAdditionalAddressArray["id"] = $eachAdditionalAddress->getId();
                        $eachAdditionalAddressArray["addressTitle"] = $eachAdditionalAddress->getAddressTitle();
                        $eachAdditionalAddressArray["value"] = $eachAdditionalAddress->format("html");
                    } else {
                        $eachAdditionalAddressArray["id"] = 0;
                        $eachAdditionalAddressArray["value"] = __(
                            "You have no other address entries in your address book."
                        );
                    }
                    $addressCount++;
                    $this->returnArray["additionalAddress"][] = $eachAdditionalAddressArray;
                }
            }
            $this->returnArray["addressCount"] = $addressCount;
            $this->returnArray["success"] = true;
            $encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->forDashboard = $this->wholeData["forDashboard"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
