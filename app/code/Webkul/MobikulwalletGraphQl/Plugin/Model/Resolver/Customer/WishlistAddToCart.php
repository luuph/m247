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

class WishlistAddToCart
{
    protected $_quoteFactory;
    protected $_helper;
    protected $walletHelper;
    protected $returnArray;
    protected $wholeData;
    protected $customerToken;
    protected $customerId;

    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->_helper       = $helper;
        $this->walletHelper  = $walletHelper;
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
        \Webkul\MobikulApiGraphQl\Model\Resolver\Customer\WishlistToCart $subject,
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
            $customerToken  = $this->wholeData["customerToken"] ?? '';
            $customerId     = $this->_helper->getCustomerByToken($customerToken) ?? 0;
            if ($customerId != "") {
                $quoteCollection = $this->_quoteFactory->create()->getCollection();
                $quoteCollection->addFieldToFilter("customer_id", $customerId);
                $quoteCollection->addOrder("updated_at", "desc");
                $quote = $quoteCollection->getFirstItem();
            }
            $cartItems       = $quote->getAllVisibleItems();
            $walletProductId = $this->walletHelper->getWalletProductId();
            foreach ($cartItems as $item) {
                if ($item->getProduct()->getId() == $walletProductId) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("You can not add other products with wallet product, and vise versa.")
                    );
                }
            }
            $this->returnArray = $proceed($field, $context, $info, $value, $args);
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
