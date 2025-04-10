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

namespace Webkul\MobikulwalletGraphQl\Plugin\Model\Resolver\Customer;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class OrderDetails {

    protected $_helper;
    protected $scopeConfig;
    protected $returnArray;
    protected $wholeData;
    protected $customerToken;
    protected $customerId;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Sales\Model\Order $_order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_helper                 = $helper;
        $this->_order                  = $_order;
        $this->scopeConfig             = $scopeConfig;
    }

    /**
     * Plugin function afterResolve
     *
     * @param \Webkul\MobikulApi\Controller\Customer\OrderDetails $subject
     * @param $result
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return void
     */
    public function afterResolve(
        \Webkul\MobikulApiGraphQl\Model\Resolver\Customer\OrderDetails $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->returnArray = [];
        $this->wholeData    = $args;
        try{
            $this->returnArray = $result;
            if ($this->returnArray) {
                $this->customerToken = $this->wholeData["customerToken"] ?? "";
                $this->customerId = $this->_helper->getCustomerByToken($this->customerToken) ?? 0;
                if ($this->customerId != 0 && $this->returnArray["success"]) {
                    $incrementId   =  isset($wholeData["incrementId"]) ? $wholeData["incrementId"] : 0;
                    $salesOrder = $this->_order->loadByIncrementId($incrementId);
                    if($salesOrder->getWalletAmount() > 0) {
                        if ($this->returnArray["paymentMethod"] != __('Webkul Wallet System')) {
                            $this->returnArray["paymentMethod"] = $this->returnArray["paymentMethod"].' + Webkul Wallet System';
                        }
                    }
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Unautorized Access.")
                    );
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
