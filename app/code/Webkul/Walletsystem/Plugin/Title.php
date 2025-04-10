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

namespace Webkul\Walletsystem\Plugin;

/**
 * Class DiscountConfigureProcess
 *
 * Removes discount block when wallet amount product is in cart.
 */
class Title
{
    
    protected const WALLET_PAYMENT_CODE = "walletsystem";
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $salesOrder;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Sales\Model\Order $salesOrder
     * @param \Webkul\Walletsystem\Helper\Data $helper
     */
    public function __construct(
        \Magento\Sales\Model\Order $salesOrder,
        \Webkul\Walletsystem\Helper\Data $helper
    ) {
        $this->salesOrder = $salesOrder;
        $this->helper = $helper;
    }

    /**
     * After get title
     *
     * @param \Magento\Payment\Model\Method\AbstractMethod $subject
     * @param string $result
     * @return string
     */
    public function afterGetTitle(\Magento\Payment\Model\Method\AbstractMethod $subject, $result)
    {
        return $checkIfPartialWalletOrder = $this->checkOrder($result);
    }

    /**
     * Check Order function
     *
     * @param string $result
     * @return string $result
     */
    public function checkOrder($result)
    {
        $orderId = $this->helper->getOrderIdFromUrl();
        if ($orderId == "") {
            return $result;
        }
        $order = $this->salesOrder->load($orderId);
        if ($order->getWalletAmount() > 0) {
            $payment = $order->getPayment();
            $method = $payment->getMethodInstance();
            $methodCode = $method->getCode();
            if ($methodCode != self::WALLET_PAYMENT_CODE) {
                return $result.' + Webkul Wallet System';
            }
        }
        return $result;
    }
}
