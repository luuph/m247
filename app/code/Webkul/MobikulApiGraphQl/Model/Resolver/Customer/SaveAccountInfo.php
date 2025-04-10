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
use Magento\Framework\App\ObjectManager;

/**
 * SaveAccountInfo resolver
 */
class SaveAccountInfo extends AbstractCustomer implements ResolverInterface
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
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            // $this->dob = $this->localeDate->formatDate($this->dob);
            $currentCustomerDataObject = $this->customerRepositoryInterface->getById($this->customerId);
            $inputData = [
                "dob" => $this->dob,
                "email" => $this->email,
                "prefix" => $this->prefix,
                "suffix" => $this->suffix,
                "taxvat" => $this->taxvat,
                "gender" => $this->gender,
                "lastname" => $this->lastName,
                "password" => $this->newPassword,
                "firstname" => $this->firstName,
                "middlename" => $this->middleName,
                "password_confirmation" => $this->confirmPassword,
                "current_password" => $this->currentPassword,
                "mobilenumber" => $this->mobile
            ];
            $this->request->setParams($inputData);
            $storeManager = $this->storeManager;
            $customerCheck = $this->customerFactory->create()->setWebsiteId(
                $storeManager->getStore()->getWebsiteId()
            )->loadByEmail($this->email);
            
            // NEW: Check if a customer already exists with the same mobile number.
            $mobileCustomerCollection = $this->customerFactory->create()->getCollection()
                ->addAttributeToFilter('mobilenumber', $this->mobile)
                ->addAttributeToFilter('entity_id', ['neq' => $this->customerId]); // Exclude the current customer

            if ($mobileCustomerCollection->getSize() > 0) {
                $this->returnArray["message"] = __("There is already an account with this WhatsApp number.");
                return $this->returnArray;
            }

            $checkCustomerId = $customerCheck->getId();
            if ($checkCustomerId > 0 && $checkCustomerId != $this->customerId) {
                $this->returnArray["message"] = __(
                    "A customer with the same email already exists in an associated website."
                );
                return $this->returnArray;
            }
            $inputData = $this->request;
            $customerCandidateDataObject = $this->populateNewCustomerDataObject($inputData, $currentCustomerDataObject);
            if ($this->doChangeEmail == 1) {
                try {
                    $this->getAuthentication()->authenticate(
                        $currentCustomerDataObject->getId(),
                        $this->currentPassword
                    );
                } catch (\Exception $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("The password doesn't match this account.")
                    );
                }
                $this->customerRepositoryInterface->save($customerCandidateDataObject);
            }
            $isPasswordChanged = false;
            if ($this->doChangePassword == 1) {
                if ($this->newPassword != $this->confirmPassword) {
                    throw new InputException(__("Password confirmation doesn't match entered password."));
                }
                $isPasswordChanged = $this->accountManagement->changePassword(
                    $this->email,
                    $this->currentPassword,
                    $this->newPassword
                );
                $this->customerRepositoryInterface->save($customerCandidateDataObject);
            }
            $this->customerRepositoryInterface->save($customerCandidateDataObject);

            //Set DOB for Arabic Storeviews
            if ($this->storeId != 1 && $this->dob && $this->email) {
                $resource = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\App\ResourceConnection::class);
                $connection = $resource->getConnection();
                $customerTable = $resource->getTableName('customer_entity');

                $select = $connection->select()
                    ->from($customerTable, ['entity_id'])
                    ->where('email = ?', $this->email);
                $customerId = $connection->fetchOne($select);

                if ($customerId) {
                    $connection->update(
                        $customerTable,
                        ['dob' => $this->dob],
                        ['entity_id = ?' => $customerId]
                    );
                }
            }
            $this->getEmailNotification()->credentialsChanged(
                $customerCandidateDataObject,
                $currentCustomerDataObject->getEmail(),
                $isPasswordChanged
            );
            $customer = $this->customerFactory->create()->load($this->customerId);
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("You saved the account information.");
            $this->returnArray["customerName"] = $customer->getName();
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->returnArray;
        } catch (\InvalidEmailOrPasswordException $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        } catch (\UserLockedException $e) {
            $this->returnArray["message"] = __(
                "The account is locked. Please wait and try again or contact %1.",
                $this->getScopeConfig()->getValue("contact/email/recipient_email")
            );
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        } catch (InputException $e) {
            $message = [];
            $message[] = __($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $message[] = $error->getMessage();
            }
            $this->returnArray["message"] = implode(",", $message);
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * Verify Request function to verify the request
     *
     * @param \Magento\Framework\App\RequestInterface $inputData
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
     * @return void
     */
    private function populateNewCustomerDataObject(
        \Magento\Framework\App\RequestInterface $inputData,
        \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
    ) {
        $attributeValues = $this->getCustomerMapper()->toFlatArray($currentCustomerData);
        $profilePic = "";
        if (isset($attributeValues['profile_picture'])) {
            $profilePic = $attributeValues['profile_picture'];
            unset($attributeValues['profile_picture']);
        }

        $customerDto = $this->customerExtractor->extract(
            "customer_account_edit",
            $inputData,
            $attributeValues
        );
        if ($this->mobile) {
            $customerDto->setCustomAttribute('mobilenumber', $this->mobile);
        }
        if ($profilePic) {
            $customerDto->setCustomAttribute('profile_picture', $profilePic);
        }
        $customerDto->setId($currentCustomerData->getId());
        if (!$customerDto->getAddresses()) {
            $customerDto->setAddresses($currentCustomerData->getAddresses());
        }
        if (!$this->doChangeEmail) {
            $customerDto->setEmail($currentCustomerData->getEmail());
        }
        return $customerDto;
    }

    /**
     * GetScopeConfig function
     *
     * @return void
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return ObjectManager::getInstance()->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        } else {
            return $this->scopeConfig;
        }
    }

    /**
     * DispatchSuccessEvent function
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customerCandidateDataObject
     * @return void
     */
    private function dispatchSuccessEvent(\Magento\Customer\Api\Data\CustomerInterface $customerCandidateDataObject)
    {
        $this->eventManager->dispatch("customer_account_edited", ["email"=>$customerCandidateDataObject->getEmail()]);
    }

    /**
     * GetCustomerMapper function
     *
     * @return void
     */
    private function getCustomerMapper()
    {
        return $this->customerMapper;
    }

    /**
     * GetAuthentication function
     *
     * @return void
     */
    private function getAuthentication()
    {
        if (!($this->authentication instanceof \Magento\Customer\Model\AuthenticationInterface)) {
            return ObjectManager::getInstance()->get(\Magento\Customer\Model\AuthenticationInterface::class);
        } else {
            return $this->authentication;
        }
    }

    /**
     * GetEmailNotification function
     *
     * @return void
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof \Magento\Customer\Model\EmailNotificationInterface)) {
            return ObjectManager::getInstance()->get(\Magento\Customer\Model\EmailNotificationInterface::class);
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * VerifyRequest function
     *
     * @return void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->dob = $this->wholeData["dob"] ?? "";
            $this->email = $this->wholeData["email"] ?? "";
            $this->mobile = $this->wholeData["mobile"] ?? "";
            $this->prefix = $this->wholeData["prefix"] ?? "";
            $this->suffix = $this->wholeData["suffix"] ?? "";
            $this->taxvat = $this->wholeData["taxvat"] ?? "";
            $this->gender = $this->wholeData["gender"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->lastName = $this->wholeData["lastName"] ?? "";
            $this->firstName = $this->wholeData["firstName"] ?? "";
            $this->middleName = $this->wholeData["middleName"] ?? "";
            $this->newPassword = $this->wholeData["newPassword"] ?? "";
            $this->doChangeEmail = $this->wholeData["doChangeEmail"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->confirmPassword = $this->wholeData["confirmPassword"] ?? "";
            $this->currentPassword = $this->wholeData["currentPassword"] ?? "";
            $this->doChangePassword = $this->wholeData["doChangePassword"] ?? 0;
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
