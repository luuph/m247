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

declare(strict_types=1);
namespace Webkul\Walletsystem\Plugin\Multishipping;

class MultishippingCheckout
{
    /**
     * @var \Webkul\Walletsystem\Helper\Multishipping
     */
    protected $multihelper;

    /**
     * Constructor
     *
     * @param \Webkul\Walletsystem\Helper\Multishipping $multihelper
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Multishipping $multihelper,
    ) {
        $this->multihelper = $multihelper;
    }
    
    /**
     * Override Total amount for Wallet Amount
     *
     * @param \Magento\Multishipping\Block\Checkout\Overview $subject
     * @param double $result
     * @return double $result
     */
    public function afterGetTotal(
        \Magento\Multishipping\Block\Checkout\Overview $subject,
        $result
    ) {
        $orderData = $this->multihelper->getOrderData();
        $addressCount = $this->multihelper->shippingAddressCount();
        $isPartial = $this->multihelper->isPartial();

        if ($orderData['method'] == 'walletsystem' || $isPartial) {
            return $result - $orderData['used_amount'] ;
        }

        return $result ;
    }

    /**
     * Override Address Total
     *
     * @param \Magento\Multishipping\Block\Checkout\Overview $subject
     * @param array $result
     * @param object $address
     * @return array $result
     */
    public function afterGetShippingAddressTotals(
        \Magento\Multishipping\Block\Checkout\Overview $subject,
        $result,
        $address
    ) {
        
        $orderData = $this->multihelper->getOrderData();
        $isPartial = $this->multihelper->isPartial();

        if ($orderData['method'] == 'walletsystem' || $isPartial) {
            $addressTotal = $result['grand_total']['value'];
            $orderTotal = $orderData['orderTotal'];

            $walletAmount = ($addressTotal / $orderTotal ) * $orderData['used_amount'];
            $grandTotal = abs($result['grand_total']['value'] - $walletAmount);

            $result['wallet_amount']['value'] = $walletAmount;
            $result['grand_total']['value'] = $grandTotal;
            
            return $result;
        }
        return $result;
    }

    /**
     * For partial method change method title
     *
     * @param \Magento\Multishipping\Block\Checkout\Overview $subject
     * @param string $result
     * @return string $result
     */
    public function afterGetPaymentHtml(
        \Magento\Multishipping\Block\Checkout\Overview $subject,
        $result
    ) {
        if ($this->multihelper->isPartial()) {
            $getTitle = $subject->getPayment()->getMethodInstance()->getTitle();
            $html = "\n<strong>".$getTitle." + Webkul Wallet System <strong></dt>\n</dl>\n";
            return $html;
        }
        return $result;
    }
}
