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

class OrderList {

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
     * @param \Webkul\MobikulApiGraphQl\Model\Resolver\Customer\OrderList $subject
     * @param $result
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return void
     */
    public function afterResolve(
        \Webkul\MobikulApiGraphQl\Model\Resolver\Customer\OrderList $subject,
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
                if ($this->returnArray && $this->customerId != 0 && $this->returnArray["success"]) {
                    foreach($this->returnArray['orderList'] as &$items) {
                        $order = $this->_order->load($items['id']);
                        $orderItems = $order->getAllVisibleItems();
                        $items['walletReview'] = false;
                        foreach ($orderItems as $item) {
                            if ($item->getSku() == "wk_wallet_amount") {
                                $items['walletReview'] = true;
                            }
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
