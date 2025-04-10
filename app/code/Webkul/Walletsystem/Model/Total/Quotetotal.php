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

namespace Webkul\Walletsystem\Model\Total;

/**
 * Webkul Walletsystem Class
 */
class Quotetotal extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Quote\Model\QuoteValidator
     */
    protected $quoteValidator = null;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    protected $walletHelper;
    
    /**
     * @var Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutsession
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Checkout\Model\Session $checkoutsession,
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->setCode('wallet');
        $this->quoteValidator = $quoteValidator;
        $this->checkoutSession = $checkoutsession;
        $this->walletHelper = $walletHelper;
        $this->cartHelper = $cartHelper;
        $this->logger = $logger;
        $this->request = $request;
    }

    /**
     * Collect totals process.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        if (!$shippingAssignment->getItems()) {
            return $this;
        }
        foreach ($quote->getAllItems() as $item) {
            if ($item->getSku() == \Webkul\Walletsystem\Helper\Data::WALLET_PRODUCT_SKU
            && $item->getQuoteId()) {
                $item->setCustomPrice($item->getOriginalCustomPrice());
                $item->save();
            }
        }
        $helper = $this->walletHelper;
        $helper->checkWalletproductWithOtherProduct();
        $address = $shippingAssignment->getShipping()->getAddress();
        $toatlsArray = $total->getAllTotalAmounts();
        $grandTotal = array_sum($toatlsArray);
        $balance = $this->getWalletamountForCart($grandTotal, $toatlsArray);
        if ($balance) {
            $currentCurrencyCode = $helper->getCurrentCurrencyCode();
            $baseCurrencyCode = $helper->getBaseCurrencyCode();
            $allowedCurrencies = $helper->getConfigAllowCurrencies();
            $rates = $helper->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
            if (empty($rates[$currentCurrencyCode])) {
                $rates[$currentCurrencyCode] = 1;
            }
            $baseAmount = $helper->baseCurrencyAmount($balance);
            $balance = -($balance);
            $baseAmount = -($baseAmount);
            $address->setData('wallet_amount', $balance);
            $address->setData('base_wallet_amount', $baseAmount);
            $total->setTotalAmount('wallet', $balance);
            $total->setBaseTotalAmount('wallet', $baseAmount);
            $quote->setWalletAmount($balance);
            $quote->setBaseWalletAmount($baseAmount);
            $total->setWalletAmount($balance);
            $total->setBaseWalletAmount($baseAmount);
        } else {
            $address->setData('wallet_amount', 0);
            $address->setData('base_wallet_amount', 0);
            $total->setTotalAmount('wallet', 0);
            $total->setBaseTotalAmount('wallet', 0);
            $quote->setWalletAmount(0);
            $quote->setBaseWalletAmount(0);
            $total->setWalletAmount(0);
            $total->setBaseWalletAmount(0);
        }
        return $this;
    }

    /**
     * Get Wallet amount For Cart function
     *
     * @param int $addressGrandTotal
     * @param array $toatlsArray
     * @return int
     */
    protected function getWalletamountForCart($addressGrandTotal, $toatlsArray)
    {
        $getSession = $this->checkoutSession->getWalletDiscount();
        $wallethelper = $this->walletHelper;
        $cartHelper = $this->cartHelper;
        $amount = 0;
        $finalWalletAmount = 0;
        $grandtotal = 0;
        if (is_array($getSession) && array_key_exists('flag', $getSession) && $getSession['flag'] == 1) {
            $totalByToatls = 0;
            foreach ($toatlsArray as $akey => $totalIns) {
                if (!in_array($akey, ['wallet_amount'])) {
                    $totalByToatls += $totalIns;
                }
            }
            $grandtotal = round($totalByToatls, 4);
            if ($getSession['grand_total'] != $grandtotal) {
                $getSession['grand_total'] = $grandtotal;
                $getSession['amount'] = 0;
                $getSession['type'] = 'reset';
                $this->checkoutSession->setWalletDiscount($getSession);
                return 0;
            }
            $amount = $getSession['amount'];
            $finalWalletAmount = $getSession['amount'];
            if ($getSession['type'] == 'set') {
                $customerId = $wallethelper->getCustomerId();
                $totalAmount = $wallethelper->currentCurrencyAmount(
                    $wallethelper->getWalletTotalAmount($customerId),
                    ''
                );
                if ($getSession['amount'] > $grandtotal) {
                    $amount = $grandtotal;
                }
                if ($amount < $grandtotal) {
                    if ($grandtotal < $totalAmount) {
                        $amount = $grandtotal;
                    } else {
                        $amount = $totalAmount;
                    }
                }
                $walletPercent = ($amount*100)/$grandtotal;
                $finalWalletAmount = ($addressGrandTotal*$walletPercent)/100;
            }
        }

        return $finalWalletAmount;
    }

    /**
     * Fetch function
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [
            'code' => $this->getCode(),
            'title' => __('Wallet Amount'),
            'value' => $total->getWalletAmount(),
        ];
    }

    /**
     * Get Label function
     *
     * @return string
     */
    public function getLabel()
    {
        return __('Wallet Amount');
    }
}
