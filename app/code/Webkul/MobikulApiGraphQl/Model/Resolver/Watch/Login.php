<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphQl
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Watch;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Login resolver
 */
class Login extends AbstractWatch implements ResolverInterface
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
            if($this->customerId != 0){
                $this->returnArray["success"] = false;
                if(isset($this->firebaseId)){
                    $firebaseId = $this->encrypted->decrypt($this->firebaseId);
                    $watchFactory = $this->watchFactory->create();
                    $filteredWatchFactoryData = $watchFactory->getCollection()
                        ->addFieldToFilter("firebase_id", $firebaseId)
                        ->getFirstItem();
                    if($filteredWatchFactoryData != null && $filteredWatchFactoryData->getId() != null){
                        $filteredWatchFactoryData->setCustomerId($this->customerId);
                        $filteredWatchFactoryData->setCustomerToken($this->customerToken);
                        $filteredWatchFactoryData->setStatus(1);
                        $filteredWatchFactoryData->save();

                        $this->tokenHelper->saveToken(
                            $this->customerId, $firebaseId, $filteredWatchFactoryData->getOs()
                        );
                        
                        // send push notification on watch
                        $message = [
                            "body" => __("You have Successfully Logged In."),
                            "title" => __("Login"),
                            "isLogin" => true,
                            "customerToken" => $filteredWatchFactoryData->getCustomerToken(),
                            "sound" => "default",
                            "message" => __("You have Successfully Logged In.")
                        ];
                        $url = self::FCMBASEURL;
                        $authKey = $this->helper->getConfigData("mobikul/notification/apikey");
                        $headers = [
                            "Authorization" => "key=".$authKey,
                            "Content-Type" => "application/json"
                        ];
                        if ($authKey != "") {
                            $isPushed = $this->getDeviceToken(
                                $firebaseId,
                                $message,
                                $url,
                                $headers,
                                $filteredWatchFactoryData->getOs()
                            );
                            if($isPushed){
                                $this->returnArray["success"] = true;
                                $this->returnArray["message"] = __("Logged in successfully.");
                            }
                            else{
                                $this->returnArray["message"] = __("Something went wrong, Notification not sent.");
                            }
                        }
                        else{
                            $this->returnArray["message"] = __("Please configure FCM push notification parameters.");
                        }
                    } 
                    else{
                        $this->returnArray["message"] = __("Qr code not matched.");
                    }
                }
                else{
                    $this->returnArray["message"] = __("firebaseId is required.");
                }
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
            $this->firebaseId = $this->wholeData["firebaseId"] ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if ($this->customerId == 0 || $this->customerToken == "") {
                $this->returnArray["success"] = false;
                $this->returnArray["message"] = __("Customer you are requesting does not exist.");
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * GetDeviceToken function
     *
     * @param string $firebaseId
     * @param mixed $message
     * @param mixed $url
     * @param mixed $headers
     * @param mixed $watch
     * @return boolean
     */
    public function getDeviceToken($firebaseId, $message, $url, $headers, $os)
    {
        $fields = [
            "to" => $firebaseId,
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        if ($os == "ios") {
            $fields["notification"] = $message;
        }

        $this->curl->setHeaders($headers);
        $this->curl->post($url, json_encode($fields));
        $result = $this->curl->getBody();
        return $this->isJson($result);
    }

    /**
     * Function isJson to check if a string is jSon
     *
     * @param string $string string
     *
     * @return bool
     */
    public function isJson($string)
    {
        $response = json_decode($string, true);
        if($response['success']){
            return (json_last_error() == JSON_ERROR_NONE);
        }
        return false;
    }
}
