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
use Magento\Framework\GraphQl\Query\ResolverInterface;

class AddToCart
{
    protected $_quoteFactory;
    protected $walletHelper;
    protected $helper;
    protected $returnArray;
    protected $wholeData;
    protected $customerToken;
    protected $customerId;

    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Webkul\MobikulCore\Helper\Data $helper,
    ) {
        $this->_quoteFactory  = $quoteFactory;
        $this->walletHelper   = $walletHelper;
        $this->helper         = $helper;
    }

    /**
     * Plugin function beforeResolve
     *
     * @param \Webkul\MobikulApiGraphQl\Model\Resolver\Customer\AddToCart $subject
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return void
     */
    public function aroundResolve(
        \Webkul\MobikulApiGraphQl\Model\Resolver\Checkout\AddToCart $subject,
        \Closure $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->returnArray = [];
        $this->wholeData = $args;
        try{
            $walletProductId = $this->walletHelper->getWalletProductId();
            $storeId = $this->wholeData["storeId"];
            $customerToken = $this->wholeData["customerToken"] ?? "";
            $customerId = $this->helper->getCustomerByToken($customerToken) ?? 0;
            $quoteId = 0;
            if ($customerId != 0) {
                $quoteCollection = $this->_quoteFactory->create()->getCollection();
                $quoteCollection->addFieldToFilter("customer_id", $customerId);
                $quoteCollection->addFieldToFilter("store_id", $storeId);
                $quoteCollection->addFieldToFilter("is_active", 1);
                $quoteCollection->addOrder("updated_at", "desc");
                $quote = $quoteCollection->getFirstItem();
                if ($quote->getEntityId()) {
                    $quoteId = $quote->getEntityId();
                }
            } else {
                $quoteId = $this->wholeData["quoteId"];
            }
            if ($quoteId != "") {
                $quote = $this->_quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                $cartItems   = $quote->getAllVisibleItems();
                foreach ($cartItems as $item) {
                    if ($item->getProduct()->getId() == $walletProductId) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("You can not add other products with wallet product, and vise versa.")
                        );
                    }
                }
            }
            $this->returnArray = $proceed($field, $context, $info, $value, $args);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray = [];
            $this->returnArray["success"] = false;
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }
}
