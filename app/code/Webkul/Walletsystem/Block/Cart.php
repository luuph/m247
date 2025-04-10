<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Block;

use Magento\Customer\Model\CustomerFactory;

/**
 * Webkul Walletsystem Class
 */
class Cart extends \Magento\Checkout\Block\Cart
{
    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var  CustomerFactory
     */
    protected $customerModel;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Webkul\Walletsystem\Helper\Data $helper
     * @param CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Webkul\Walletsystem\Helper\Data $helper,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $catalogUrlBuilder,
            $cartHelper,
            $httpContext,
            $data
        );
        $this->cartHelper = $cartHelper;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->customerModel = $customerFactory;
    }

    /**
     * Check whether wallet product is added in cart or not
     *
     * @return bool
     */
    public function getWalletInCart()
    {
        $walletProductId = $this->helper->getWalletProductId();
        $cartData = $this->checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($cartData as $item) {
            if ($item->getProduct()->getId() == $walletProductId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get wallet amount details of logged in customer
     *
     * @return void
     */
    public function getWalletDetailsData()
    {
        $walletProductId = $this->helper->getWalletProductId();
        $customerId = $this->helper->getCustomerId();
        $customerName = $this->customerModel
            ->create()
            ->load($customerId)
            ->getName();
        $currencySymbol = $this->helper->getCurrencySymbol(
            $this->helper->getCurrentCurrencyCode()
        );
        $currentWalletamount = $this->helper->getWalletTotalAmount($customerId);
        $currentWalletamount = $this->convertAmountToCurrent($currentWalletamount);
        $returnDataArray = [
            'customer_name' => $customerName,
            'wallet_amount' => $currentWalletamount,
            'walletProductId' => $walletProductId,
            'currencySymbol' => $currencySymbol
        ];
        return $returnDataArray;
    }

    /**
     * Get item delete post json link
     *
     * @param object $item
     * @return void
     */
    public function getDeletePostJson($item)
    {
        return $this->cartHelper->getDeletePostJson($item);
    }

    /**
     * Get calculated credit rules data
     */
    public function getCreditRuleData()
    {
        return $this->helper->calculateCreditAmountforCart();
    }

    /**
     * Get formatted price
     *
     * @param string $price
     * @return string
     */
    public function getFormattedPrice($price)
    {
        return $this->helper->getFormattedPrice($price);
    }

    /**
     * Convert amount to currency currency
     *
     * @param int $amount
     * @return int
     */
    public function convertAmountToCurrent($amount)
    {
        $base = $this->helper->getBaseCurrencyCode();
        $current = $this->helper->getCurrentCurrencyCode();
        $returnamount = $this->helper->getwkconvertCurrency($base, $current, $amount);
        return $returnamount;
    }
}
