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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Model;

use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Model\Calculation\AbstractAggregateCalculator;
use Magento\Tax\Model\Calculation\TotalBaseCalculator as DefaultTotalBaseCalculator;

class AggregateCalculator extends AbstractAggregateCalculator
{
    /**
     * @param float $amount
     * @param null|string $rate
     * @param null|bool $direction
     * @param string $type
     * @param bool $round
     * @param null|mixed $item
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function roundAmountTotalBase(
        $amount,
        $rate = null,
        $direction = null,
        $type = self::KEY_REGULAR_DELTA_ROUNDING,
        $round = true,
        $item = null
    ) {
        return $this->deltaRound($amount, $rate, $direction, $type, $round);
    }

    /**
     * @param float $amount
     * @param null|string $rate
     * @param null|bool $direction
     * @param string $type
     * @param bool $round
     * @param null|mixed $item
     * @return float
     */
    protected function roundAmount(
        $amount,
        $rate = null,
        $direction = null,
        $type = self::KEY_REGULAR_DELTA_ROUNDING,
        $round = true,
        $item = null
    ) {
        if ($item->getAssociatedItemCode()) {
            // Use delta rounding of the product's instead of the weee's
            $type = $type . $item->getAssociatedItemCode();
        } else {
            $type = $type . $item->getCode();
        }

        return $this->deltaRound($amount, $rate, $direction, $type, $round);
    }

    /**
     * @param float $rowTaxExact
     * @param string $rate
     * @param bool $direction
     * @param string $deltaRoundingType
     * @param bool $round
     * @param mixed $item
     * @param string $typeClass
     * @return float
     */
    protected function returnRowTaxByTypeClass(
        $rowTaxExact,
        $rate,
        $direction,
        $deltaRoundingType,
        $round,
        $item,
        $typeClass
    ) {
        if ($typeClass == 'row') {
            $this->roundAmount($rowTaxExact, $rate, $direction, $deltaRoundingType, $round, $item);
        }
        return $this->roundAmountTotalBase($rowTaxExact, $rate, $direction, $deltaRoundingType, $round, $item);
    }

    /**
     * Calculate Tax Price by BSS.
     *
     * @param QuoteDetailsItemInterface $item
     * @param int $quantity
     * @param bool $round
     * @param string $typeClass
     * @param mixed $taxRateRequest
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface|DefaultTotalBaseCalculator
     */
    public function calculateTaxPriceWhenModuleEnable($item, $quantity, $round, $typeClass = 'row', $taxRateRequest = null)
    {
        $taxRateRequest = $taxRateRequest ?? $this->getAddressRateRequest()->setProductClassId(
            $this->taxClassManagement->getTaxClassId($item->getTaxClassKey())
        );
        $rate = $this->calculationTool->getRate($taxRateRequest);
        $storeRate = $storeRate = $this->calculationTool->getStoreRate($taxRateRequest, $this->storeId);

        $discountTaxCompensationAmount = 0;
        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($this->storeId);
        $discountAmount = $item->getDiscountAmount();

        $priceInclTax = $this->calculationTool->round($item->getUnitPrice());
        $rowTotalInclTax = $priceInclTax * $quantity + $item->getAbsoluteAmount();
        if (!$this->isSameRateAsStore($rate, $storeRate)) {
            $priceInclTax = $this->calculatePriceInclTax($priceInclTax, $storeRate, $rate, $round);
            $absInclTax = $this->calculatePriceInclTax($item->getAbsoluteAmount(), $storeRate, $rate, $round);
            $rowTotalInclTax = $priceInclTax * $quantity + $absInclTax;
        }
        $rowTaxExact = $this->calculationTool->calcTaxAmount($rowTotalInclTax, $rate, true, false);

        $deltaRoundingType = self::KEY_REGULAR_DELTA_ROUNDING;
        if ($applyTaxAfterDiscount) {
            $deltaRoundingType = self::KEY_TAX_BEFORE_DISCOUNT_DELTA_ROUNDING;
        }

        $rowTax = $this->returnRowTaxByTypeClass(
            $rowTaxExact,
            $rate,
            true,
            $deltaRoundingType,
            $round,
            $item,
            $typeClass
        );
        $rowTotal = $rowTotalInclTax - $rowTax;
        $price = ($rowTotal - $item->getAbsoluteAmount()) / $quantity;
        if ($round) {
            $price = $this->calculationTool->round($price);
        }

        if ($applyTaxAfterDiscount) {
            $taxableAmount = max($rowTotalInclTax - $discountAmount, 0);
            $rowTaxAfterDiscount = $this->calculationTool->calcTaxAmount(
                $taxableAmount,
                $rate,
                true,
                false
            );
            $rowTaxAfterDiscount = $this->returnRowTaxByTypeClass(
                $rowTaxAfterDiscount,
                $rate,
                true,
                self::KEY_REGULAR_DELTA_ROUNDING,
                $round,
                $item,
                $typeClass
            );

            $discountTaxCompensationAmount = $rowTax - $rowTaxAfterDiscount;
            $rowTax = $rowTaxAfterDiscount;
        }

        $appliedRates = $this->calculationTool->getAppliedRates($taxRateRequest);
        $appliedTaxes = $this->getAppliedTaxes($rowTax, $rate, $appliedRates);

        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode($item->getCode())
            ->setType($item->getType())
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($rowTotal)
            ->setRowTotalInclTax($rowTotalInclTax)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setAssociatedItemCode($item->getAssociatedItemCode())
            ->setTaxPercent($rate)
            ->setAppliedTaxes($appliedTaxes);
    }

    /**
     * Calculate Tax Not In Price by BSS.
     *
     * @param QuoteDetailsItemInterface $item
     * @param int $quantity
     * @param bool $round
     * @param string $typeClass
     * @param mixed $taxRateRequest
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface|DefaultTotalBaseCalculator
     */
    public function calculateTaxNotInPriceWhenModuleEnable($item, $quantity, $round, $typeClass = 'row', $taxRateRequest = null)
    {
        $taxRateRequest = $taxRateRequest ?? $this->getAddressRateRequest()->setProductClassId(
            $this->taxClassManagement->getTaxClassId($item->getTaxClassKey())
        );
        $rate = $this->calculationTool->getRate($taxRateRequest);
        $appliedRates = $this->calculationTool->getAppliedRates($taxRateRequest);

        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($this->storeId);
        $discountAmount = $item->getDiscountAmount();
        $discountTaxCompensationAmount = 0;

        $price = $this->calculationTool->round($item->getUnitPrice());
        $rowTotal = $price * $quantity + $item->getAbsoluteAmount();
        $rowTaxes = [];
        $rowTaxesBeforeDiscount = [];
        $appliedTaxes = [];

        foreach ($appliedRates as $appliedRate) {
            $taxId = $appliedRate['id'];
            $taxRate = $appliedRate['percent'];
            $rowTaxPerRate = $this->calculationTool->calcTaxAmount($rowTotal, $taxRate, false, false);
            $deltaRoundingType = self::KEY_REGULAR_DELTA_ROUNDING;
            if ($applyTaxAfterDiscount) {
                $deltaRoundingType = self::KEY_TAX_BEFORE_DISCOUNT_DELTA_ROUNDING;
            }
            $rowTaxPerRate = $this->returnRowTaxByTypeClass(
                $rowTaxPerRate,
                $taxId,
                false,
                $deltaRoundingType,
                $round,
                $item,
                $typeClass
            );
            $rowTaxAfterDiscount = $rowTaxPerRate;

            if ($applyTaxAfterDiscount) {
                $taxableAmount = max($rowTotal - $discountAmount, 0);
                $rowTaxAfterDiscount = $this->calculationTool->calcTaxAmount(
                    $taxableAmount,
                    $taxRate,
                    false,
                    false
                );
                $rowTaxAfterDiscount = $this->returnRowTaxByTypeClass(
                    $rowTaxAfterDiscount,
                    $taxId,
                    false,
                    self::KEY_REGULAR_DELTA_ROUNDING,
                    $round,
                    $item,
                    $typeClass
                );
            }
            $appliedTaxes[$taxId] = $this->getAppliedTax(
                $rowTaxAfterDiscount,
                $appliedRate
            );

            $rowTaxes[] = $rowTaxAfterDiscount;
            $rowTaxesBeforeDiscount[] = $rowTaxPerRate;
        }
        $rowTax = array_sum($rowTaxes);
        $rowTaxBeforeDiscount = array_sum($rowTaxesBeforeDiscount);
        $rowTotalInclTax = $rowTotal + $rowTaxBeforeDiscount;
        $priceInclTax = ($rowTotalInclTax - $item->getAbsoluteAmount()) / $quantity;
        if ($round) {
            $priceInclTax = $this->calculationTool->round($priceInclTax);
        }

        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode($item->getCode())
            ->setType($item->getType())
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($rowTotal)
            ->setRowTotalInclTax($rowTotalInclTax)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setAssociatedItemCode($item->getAssociatedItemCode())
            ->setTaxPercent($rate)
            ->setAppliedTaxes($appliedTaxes);
    }
}
