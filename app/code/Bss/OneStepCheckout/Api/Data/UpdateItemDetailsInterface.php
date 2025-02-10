<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OneStepCheckout
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\OneStepCheckout\Api\Data;

/**
 * Interface UpdateItemDetailsInterface
 * @api
 */
interface UpdateItemDetailsInterface
{
    /**
     * Constants defined for keys of array, makes typos less likely
     */
    const PAYMENT_METHODS = 'payment_methods';

    const TOTALS = 'totals';

    const SHIPPING_METHODS = 'shipping_methods';

    const MESSAGE = 'message';

    const STATUS = 'status';

    const HAS_ERROR = 'has_error';

    const GIFT_WRAP_DISPLAY = 'gift_wrap_display';

    const GIFT_WRAP_LABEL = 'gift_wrap_label';

    const QTY_BEFORE = 'qty_before';

    /**
     * Get payment methods
     *
     * @return \Magento\Quote\Api\Data\PaymentMethodInterface[]
     */
    public function getPaymentMethods();

    /**
     * Set payment methods
     *
     * @param \Magento\Quote\Api\Data\PaymentMethodInterface[] $paymentMethods
     * @return $this
     */
    public function setPaymentMethods($paymentMethods);

    /**
     * Get totals
     *
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function getTotals();

    /**
     * Set totals
     *
     * @param \Magento\Quote\Api\Data\TotalsInterface $totals
     * @return $this
     */
    public function setTotals($totals);

    /**
     * Get shipping methods
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function getShippingMethods();

    /**
     * Set shipping methods
     *
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface[] $shippingMethods
     * @return $this
     */
    public function setShippingMethods($shippingMethods);

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * Get status
     *
     * @return bool
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param bool $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get has error
     *
     * @return bool
     */
    public function getHasError();

    /**
     * Set has error
     *
     * @param bool $error
     * @return $this
     */
    public function setHasError($error);

    /**
     * Get gift wrap display
     *
     * @return bool
     */
    public function getGiftWrapDisplay();

    /**
     * Set gift wrap display
     *
     * @param bool $display
     * @return $this
     */
    public function setGiftWrapDisplay($display);

    /**
     * Get gift wrap label
     *
     * @return string
     */
    public function getGiftWrapLabel();

    /**
     * Set gift wrap label
     *
     * @param string $label
     * @return $this
     */
    public function setGiftWrapLabel($label);

    /**
     * Get qty before
     *
     * @return int|float
     */
    public function getQtyBefore();

    /**
     * Set qty before
     *
     * @param int|float $qtyBefore
     * @return $this
     */
    public function setQtyBefore($qtyBefore);
}
