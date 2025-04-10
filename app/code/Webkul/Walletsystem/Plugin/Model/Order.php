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

namespace Webkul\Walletsystem\Plugin\Model;

/**
 * Class DiscountConfigureProcess
 *
 * Removes discount block when wallet amount product is in cart.
 */
class Order
{

    /**
     * After Get Grand Total function
     *
     * @param \Magento\Sales\Model\Order\Payment $subject
     * @param string $result
     * @return string $result
     */
    public function afterGetGrandTotal(\Magento\Sales\Model\Order $subject, $result)
    {
        $source = $subject;
        if (!$subject->getBaseTotalInvoiced() && (int)$subject->getWalletAmount()) {
            return $amount = $source->getSubtotal() + (int) $source->getShippingAmount();
        } else {
            return $result;
        }
    }

    /**
     * After Get Base Total function
     *
     * @param \Magento\Sales\Model\Order\Payment $subject
     * @param string $result
     * @return string $result
     */
    public function afterGetTotalDue(\Magento\Sales\Model\Order $subject, $result)
    {
        if ($subject->getGrandTotal()!=$subject->getTotalPaid()
        && $subject->getPayment()
        && ($subject->getPayment()->getMethod() != 'walletsystem')
        && (int)$subject->getWalletAmount()) {
            return $amount = $result - $subject->getWalletAmount();
        }
        return $result;
    }
    
    /**
     * After Get Base Total Due function
     *
     * @param \Magento\Sales\Model\Order\Payment $subject
     * @param string $result
     * @return string $result
     */
    public function afterGetBaseTotalDue(\Magento\Sales\Model\Order $subject, $result)
    {
        if ($subject->getGrandTotal()!=$subject->getTotalPaid()
        && $subject->getPayment() && ($subject->getPayment()->getMethod() != 'walletsystem')
        && (int)$subject->getBaseWalletAmount()) {
            return $amount = $result + $subject->getBaseWalletAmount();
        }
        return $result;
    }
}
