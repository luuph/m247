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

namespace Webkul\MobikulwalletGraphQl\Plugin\Model\Resolver\Checkout;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetCartDetails {

    protected $_helper;
    protected $_walletHelper;
    protected $_customerSession;
    protected $returnArray;
    protected $wholeData;
    protected $customerToken;
    protected $customerId;

    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_helper          = $helper;
        $this->_walletHelper    = $walletHelper;
        $this->_customerSession = $customerSession;
    }

    /**
     * Plugin function beforeResolve
     *
     * @param \Webkul\MobikulApiGraphQl\Model\Resolver\Checkout\CartDetails $subject
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return void
     */
    public function afterResolve(
        \Webkul\MobikulApiGraphQl\Model\Resolver\Checkout\CartDetails $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->returnArray = [];
        $this->wholeData = $args;
        try{
            $this->returnArray = $result;
            if ($this->returnArray) {
                $customerId = $this->_helper->getCustomerByToken($this->wholeData["customerToken"] ?? "") ?? 0;
                if ($this->returnArray["success"] == 1 && $customerId != 0) {
                    $this->_customerSession->setCustomerId($customerId);
                    $walletData      = $this->_walletHelper->getWalletDetailsData();
                    $walletProductId = $this->_walletHelper->getWalletProductId();
                    $customerName = $walletData["customer_name"];
                    $walletAmount = $walletData["currencySymbol"].$walletData["wallet_amount"];
                    $allItems = [];
                    if(!empty($this->returnArray['items'])){
                        foreach ($this->returnArray['items'] as $item) {
                            if ($item['productId'] == $walletProductId) {
                                $item['options'][] = [
                                    "label" => __("Wallet Holder's Name"),
                                    "value" => [$customerName]
                                ];
                                $item['options'][] = [
                                    "label" => __("Current Amount"),
                                    "value" => [$walletAmount]
                                ];
                            }
                            $allItems[] = $item;
                        }
                    }
                    $this->returnArray['items'] = $allItems;
                    $this->_customerSession->unsCustomerId();
                }
            }
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray = [];
            $this->returnArray["success"] = false;
            $this->returnArray["message"] = __($e->getMessage());
            $this->_helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }
}
