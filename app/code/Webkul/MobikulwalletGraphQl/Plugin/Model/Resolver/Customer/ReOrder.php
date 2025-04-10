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

class ReOrder {
    protected $_quoteFactory;
    protected $_helper;
    protected $walletHelper;
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
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Sales\Model\Order $_order,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->_helper       = $helper;
        $this->walletHelper  = $walletHelper;
        $this->_order        = $_order;
        $this->_quoteFactory = $quoteFactory;
    }

    /**
     * Plugin function aroundResolve
     *
     * @param \Webkul\MobikulApiGraphQl\Model\Resolver\Customer\ReOrder $subject
     * @param \Closure $proceed
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return void
     */
    public function aroundResolve(
        \Webkul\MobikulApiGraphQl\Model\Resolver\Customer\ReOrder $subject,
        \Closure $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->returnArray = [];
        $this->wholeData    = $args;
        try{
            $incrementId   = $this->wholeData["incrementId"];
            $customerToken = $this->wholeData["customerToken"];
            $customerId    = $this->_helper->getCustomerByToken($customerToken) ?? 0;
            $storeId       = $this->wholeData["storeId"];
            if ($customerId != "") {
                $quoteCollection = $this->_quoteFactory->create()->getCollection();
                $quoteCollection->addFieldToFilter("customer_id", $customerId);
                $quoteCollection->addFieldToFilter("store_id", $storeId);
                $quoteCollection->addFieldToFilter("is_active", 1);
                $quoteCollection->addOrder("updated_at", "desc");
                $quote = $quoteCollection->getFirstItem();
            }
            $order           = $this->_order->loadByIncrementId($incrementId);
            $cartItems       = $quote->getAllVisibleItems();
            $returnArray     = [];
            $walletProductId = $this->walletHelper->getWalletProductId();
            $price = 0;
            foreach ($order->getItemsCollection() as $item) {
                if ($item->getProduct()->getId() == $walletProductId) {
                    $price = $item->getPrice();
                }
            }
            $otherItems = false;
            $walletItem = false;
            $updated = false;
            foreach($cartItems as $item) {
                if ($item->getProduct()->getId() == $walletProductId) {
                    $walletItem = true;
                    $price = $item->getCustomPrice() + $price;
                    $item->setCustomPrice($price);
                    $item->setOriginalCustomPrice($price);
                    $item->setQty(1);
                    $item->getProduct()->setIsSuperMode(true);
                    $item->setRowTotal($price);
                    $item->save();
                    $updated = true;
                } else {
                    $otherItems = true;
                }
            }
            foreach ($order->getItemsCollection() as $item) {
                if ($item->getProduct()->getId() != $walletProductId && $walletItem) {
                    $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("You can not add other product with wallet product.")
                    );
                } elseif ($item->getProduct()->getId() == $walletProductId && $otherItems) {
                    $this->_helper->printLog($this->wholeData);
                    $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("You can not add wallet product with other product.")
                    );
                }
            }
            $this->returnArray = $proceed($field, $context, $info, $value, $args);
            if (count($cartItems) && $updated) {
                $quote->collectTotals()->save();
                $this->returnArray["success"] = true;
                $this->returnArray["message"] = __("Product(s) has been added to cart.");
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