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
 * @category  BSS
 * @package   Bss_OneStepCheckout
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\OneStepCheckout\Model\Total\Quote;

class GiftWrap extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Bss\OneStepCheckout\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Bss\OneStepCheckout\Helper\Data
     */
    protected $dataHelper;

    /**
     * Gift wrap constructor.
     *
     * @param \Bss\OneStepCheckout\Helper\Config $configHelper
     * @param \Bss\OneStepCheckout\Helper\Data $dataHelper
     */
    public function __construct(
        \Bss\OneStepCheckout\Helper\Config $configHelper,
        \Bss\OneStepCheckout\Helper\Data $dataHelper
    ) {
        $this->configHelper = $configHelper;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this|\Magento\Quote\Model\Quote\Address\Total\AbstractTotal
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $total->setTotalAmount('osc_gift_wrap', 0);
        $total->setBaseTotalAmount('base_osc_gift_wrap', 0);

        if (empty($shippingAssignment->getItems())) {
            return $this;
        }
        $this->prepareGiftWrap($quote);

        if ($quote->getOscGiftWrap() !== null) {
            $total->setTotalAmount('osc_gift_wrap', $quote->getOscGiftWrap());
            $total->setBaseTotalAmount('base_osc_gift_wrap', $quote->getBaseOscGiftWrap());
        }

        return $this;
    }

    /**
     * Prepare giftWrap before checkout
     * If disable Bss_OSC -> Set GiftWrap to null
     * If enable Bss_OSC -> compatible multi currency
     *
     * @param $quote
     * @return void
     */
    public function prepareGiftWrap($quote)
    {
        if ($quote && $quote->getId()) {
            $storeId = $quote->getStoreId();
            $giftWrapFeeConfig = $this->configHelper->getGiftWrapFee();
            $currentGiftWrapFee = $quote->getOscGiftWrap();
            if ($giftWrapFeeConfig === false || $quote->isVirtual()) {
                $quote->setBaseOscGiftWrapFeeConfig(null);
                $quote->setOscGiftWrapFeeConfig(null);
                $quote->setOscGiftWrapType(null);
                $quote->setBaseOscGiftWrap(null);
                $quote->setOscGiftWrap(null);
            } elseif ($currentGiftWrapFee !== null) {
                $giftWrapType = $this->configHelper->getGiftWrap('type', $storeId);
                $giftWrapFeeConfigCurrency = $this->configHelper->formatCurrency($giftWrapFeeConfig);
                $giftWrapFee = $this->dataHelper->getTotalGiftWrapFee($quote, $giftWrapFeeConfig, $giftWrapType);
                $giftWrapFeeCurrency = $this->configHelper->formatCurrency($giftWrapFee);
                if ($giftWrapFeeCurrency != $currentGiftWrapFee) {
                    $quote->setBaseOscGiftWrapFeeConfig($giftWrapFeeConfig);
                    $quote->setOscGiftWrapFeeConfig($giftWrapFeeConfigCurrency);
                    $quote->setOscGiftWrapType($giftWrapType);
                    $quote->setBaseOscGiftWrap($giftWrapFee);
                    $quote->setOscGiftWrap($giftWrapFeeCurrency);
                }
            }
        }
    }

    /**
     * Fetch gift wrap
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {

        if ($quote->getOscGiftWrap() !== null && $quote->getOscGiftWrapType() !== null) {
            return [
                'code' => 'osc_gift_wrap',
                'title' => __('Gift Wrap'),
                'value' => $quote->getOscGiftWrap(),
            ];
        }
        return null;
    }
}
