<?php
/**
* Webkul Software.
*
* @category  Webkul
* @package   Webkul_MobikulwalletGraphQl
* @author    Webkul Software Private Limited
* @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
* @license   https://store.webkul.com/license.html
*/
namespace Webkul\MobikulwalletGraphQl\Model;

use Webkul\MobikulwalletGraphQl\Api\AccountdetailsInterface;

class Accountdetails implements AccountdetailsInterface
{
    /**
     * @var \Webkul\Walletsystem\Model\AccountDetails
     */
    protected $accountDetails;
    protected $mobikulHelper;
    protected $request;

    /**
     *
     * @param \Webkul\Walletsystem\Model\AccountDetails $accountDetails
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $mobikulHelper,
        \Magento\Framework\App\Request\Http $request,
        \Webkul\Walletsystem\Model\AccountDetails $accountDetails,
        \Webkul\MobikulwalletGraphQl\Api\ResponseInterface $walletResponse
    ){
        $this->accountDetails   = $accountDetails;
        $this->request          = $request;
        $this->walletResponse   = $walletResponse;
        $this->mobikulHelper    = $mobikulHelper;
    }

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $customerToken Users id.
     * @return \Webkul\MobikulwalletGraphQl\Api\ResponseInterface.
     */
    public function getAccountDetails($customerToken = NULL) {
        $customerId = $this->mobikulHelper->getCustomerByToken($customerToken) ?? 0;
        $authKey    = $this->request->getHeader("authKey");
        $authData   = $this->mobikulHelper->isAuthorized($authKey);
        $response   = $this->walletResponse;
        if ($authData["code"] == 1) {
            if ($customerId) {
                $accountDataColection = $this->accountDetails->getCollection()
                                        ->addFieldToFilter('customer_id', $customerId)
                                        ->addFieldToFilter('status', ['neq' => 0])
                                        ->setOrder('entity_id', 'DSC');
                $returnArray = [];
                foreach($accountDataColection as $model) {
                    $returnArray[] = [
                        "id" => $model->getId(),
                        "holdername" => $model->getHoldername(),
                        "bankname" => $model->getBankname(),
                        "bankcode" => $model->getBankcode(),
                        "additional" => $model->getAdditional()
                    ];
                }
                $response->setResponseData(json_encode($returnArray, true));
                $response->setSuccess(true);
                $response->setMessage("");
            } else {
                $response->setMessage(__("Sorry You Are Not Authorised to access this request."));
                $response->setSuccess(false);
            }
        } else {
            $response->setSuccess(false);
            header("token: ".$authData["token"]);
            throw new \Magento\Framework\Oauth\Exception(__("Invalid Request"));
        }
        return $response;
    }

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $customerToken Users id.
     * @param int $id Users id.
     * @return \Webkul\MobikulwalletGraphQl\Api\ResponseInterface.
     */
    public function deleteRequest(
        $customerToken = NULL,
        $id = 0
    ) {
        $customerId = $this->mobikulHelper->getCustomerByToken($customerToken) ?? 0;
        $authKey    = $this->request->getHeader("authKey");
        $authData   = $this->mobikulHelper->isAuthorized($authKey);
        $response   = $this->walletResponse;
        if ($authData["code"] == 1) {
            if ($customerId && $id) {
                $this->accountDetails->load($id)
                                    ->setRequestToDelete('1')
                                    ->save();
                $response->setSuccess(true);
                $response->setMessage(__('Request Has Been Submitted To Admin'));
            } else {
                $response->setMessage(__("Sorry You Are Not Authorised to access this request."));
                $response->setSuccess(false);
            }
        } else {
            $response->setSuccess(false);
            header("token: ".$authData["token"]);
            throw new \Magento\Framework\Oauth\Exception(__("Invalid Request"));
        }
        return $response;
    }

    /**
     * Returns message and status
     *
     * @api
     * @param string $customerToken Users id.
     * @param string $holdername
     * @param string $bankname
     * @param string $bankcode
     * @param string $additional
     * @return \Webkul\MobikulwalletGraphQl\Api\ResponseInterface.
     */
    public function saveAccountDetails(
        $customerToken = NULL,
        $holdername = "",
        $bankname = "",
        $bankcode = "",
        $additional = ""
    ) {
        $customerId = $this->mobikulHelper->getCustomerByToken($customerToken) ?? 0;
        $authKey    = $this->request->getHeader("authKey");
        $authData   = $this->mobikulHelper->isAuthorized($authKey);
        $response   = $this->walletResponse;
        if ($authData["code"] == 1) {
            if ($customerId ) {
                if ($holdername != "" && $bankname != "" && $bankcode != "") {
                    $accountDetails = [
                        "holdername"    => $holdername,
                        "bankname"      => $bankname,
                        "bankcode"      => $bankcode,
                        "additional"    => $additional,
                        "customer_id"   => $customerId
                    ];
                    $this->accountDetails->setData($accountDetails)
                                        ->save();
                    $response->setSuccess(true);
                    $response->setMessage(__('Account Information Saved Successfully'));
                } else {
                    $response->setMessage(__("Invalid Data."));
                    $response->setSuccess(false);
                }
            } else {
                $response->setMessage(__("Sorry You Are Not Authorised to access this request."));
                $response->setSuccess(false);
            }
        } else {
            $response->setSuccess(false);
            header("token: ".$authData["token"]);
            throw new \Magento\Framework\Oauth\Exception(__("Invalid Request"));
        }
        return $response;
    }
}
