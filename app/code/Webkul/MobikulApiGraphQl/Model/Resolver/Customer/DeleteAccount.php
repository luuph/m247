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
 * Delete Account resolver
 */
class DeleteAccount extends AbstractCustomer implements ResolverInterface
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
            $customerModel = $this->customerFactory->create();
            $this->customer = $customerModel->load($this->customerId);
            if ($this->customer->getId() > 0) {
                $this->customerId = $this->customer->getId();
                $this->customer = $customerModel->setWebsiteId($this->websiteId);
                $hash = $customerModel->getPasswordHash();
                $validatePassword = false;
                if ($this->password !== $this->confirmPassword) {
                    $this->returnArray["message"] = __("Password does not match.");
                    return $this->returnArray;
                }
                if (!$hash) {
                    $this->returnArray["message"] = __("Invalid password.");
                    return $this->returnArray;
                }
                $validatePassword = $this->encryptor->validateHash($this->password, $hash);
                if (!$validatePassword) {
                    $this->returnArray["message"] = __("Invalid password.");
                    return $this->returnArray;
                }
                $collection = $this->deviceTokenFactory
                    ->create()
                    ->getCollection()
                    ->addFieldToFilter("customer_id", $this->customerId);
                
                foreach ($collection as $eachToken) {
                    $this->deviceTokenFactory->create()->load($eachToken->getId())->setCustomerId(0)->save();
                }
                $this->coreRegistry->register('isSecureArea', true);
                $this->customerSession->unsetAll();
                $this->coreSession->unsetAll();
                $this->customerRepositoryInterface->deleteById($this->customerId);
                $this->returnArray['success'] = true;
                $this->returnArray['message'] = __("Customer removed successfully.");
            } else {
                $this->returnArray["message"] = __("Customer does not exist.");
            }
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
            $this->websiteId = $this->wholeData["websiteId"] ?? 1;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->password = $this->wholeData["password"] ?? "";
            $this->confirmPassword = $this->wholeData["confirmPassword"] ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId  = $this->helper->getCustomerByToken($this->customerToken);
            // Checking customer token //////////////////////////////////////////////
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
