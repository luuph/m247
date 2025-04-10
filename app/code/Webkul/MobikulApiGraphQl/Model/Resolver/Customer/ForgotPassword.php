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
 * ForgotPassword resolver
 */
class ForgotPassword extends AbstractCustomer implements ResolverInterface
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
            $emailValidator = new \Zend\Validator\EmailAddress();
            if (!$emailValidator->isValid($this->email)) {
                $this->returnArray["message"] = __("Invalid email address.");
                return $this->returnArray;
            }
            $customer = $this->customerFactory->create()->setWebsiteId($this->websiteId)->loadByEmail($this->email);
            if ($customer->getId() > 0) {
                try {
                    $this->accountManagement->initiatePasswordReset(
                        $this->email,
                        \Magento\Customer\Model\AccountManagement::EMAIL_REMINDER,
                        $customer->getWebsiteId()
                    );
                    $this->returnArray["success"] = true;
                    $this->returnArray["message"] = __(
                        "If there is an account associated with %1 you will receive
                         an email with a link to reset your password.",
                        $this->email
                    );
                } catch (\Exception $e) {
                    $this->returnArray["message"] = $e->getMessage();
                    return $this->returnArray;
                }
            } else {
                $this->returnArray["success"] = true;
                $this->returnArray["message"] = __(
                    "If there is an account associated with %1 you will receive
                     an email with a link to reset your password.",
                    $this->email
                );
            }
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
            $this->email = $this->wholeData["email"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->websiteId = $this->wholeData["websiteId"] ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
