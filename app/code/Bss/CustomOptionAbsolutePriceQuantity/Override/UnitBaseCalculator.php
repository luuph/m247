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
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Override;

use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Calculation\UnitBaseCalculator as DefaultUnitBaseCalculator;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;

class UnitBaseCalculator extends DefaultUnitBaseCalculator
{
    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;
    /**
     * UnitBaseCalculator constructor.
     * @param TaxClassManagementInterface $taxClassService
     * @param TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory
     * @param AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory
     * @param AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory
     * @param Calculation $calculationTool
     * @param \Magento\Tax\Model\Config $config
     * @param int $storeId
     * @param ModuleConfig $moduleConfig
     * @param \Magento\Framework\DataObject|null $addressRateRequest
     */
    public function __construct(
        TaxClassManagementInterface $taxClassService,
        TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory,
        AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory,
        AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory,
        Calculation $calculationTool,
        \Magento\Tax\Model\Config $config,
        $storeId,
        ModuleConfig $moduleConfig,
        \Magento\Framework\DataObject $addressRateRequest = null
    ) {
        $this->moduleConfig = $moduleConfig;
        parent::__construct(
            $taxClassService,
            $taxDetailsItemDataObjectFactory,
            $appliedTaxDataObjectFactory,
            $appliedTaxRateDataObjectFactory,
            $calculationTool,
            $config,
            $storeId,
            $addressRateRequest
        );
    }

    /**
     * @param QuoteDetailsItemInterface $item
     * @param int $quantity
     * @param bool $round
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface|DefaultUnitBaseCalculator
     */
    protected function calculateWithTaxInPrice(QuoteDetailsItemInterface $item, $quantity, $round = true)
    {
        if ($this->moduleConfig->isModuleEnable()) {
            return $this->calculateTaxPriceWhenModuleEnable($item, $quantity, $round);
        }
        return parent::calculateWithTaxInPrice($item, $quantity, $round);
    }

    /**
     * @param QuoteDetailsItemInterface $item
     * @param int $quantity
     * @param bool $round
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface|DefaultUnitBaseCalculator
     */
    protected function calculateTaxPriceWhenModuleEnable($item, $quantity, $round)
    {
        $taxRateRequest = $this->getAddressRateRequest()->setProductClassId(
            $this->taxClassManagement->getTaxClassId($item->getTaxClassKey())
        );
        $rate = $this->calculationTool->getRate($taxRateRequest);
        $storeRate = $storeRate = $this->calculationTool->getStoreRate($taxRateRequest, $this->storeId);

        // Calculate $priceInclTax
        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($this->storeId);
        $priceInclTax = $this->calculationTool->round($item->getUnitPrice());
        $absInclAmount = $this->calculationTool->round($item->getAbsoluteAmount());
        if (!$this->isSameRateAsStore($rate, $storeRate)) {
            $priceInclTax = $this->calculatePriceInclTax($priceInclTax, $storeRate, $rate, $round);
            $absInclAmount = $this->calculatePriceInclTax($absInclAmount, $storeRate, $rate, $round);
        }
        $uniTax = $this->calculationTool->calcTaxAmount($priceInclTax, $rate, true, false);
        $deltaRoundingType = self::KEY_REGULAR_DELTA_ROUNDING;
        if ($applyTaxAfterDiscount) {
            $deltaRoundingType = self::KEY_TAX_BEFORE_DISCOUNT_DELTA_ROUNDING;
        }
        $uniTax = $this->roundAmount($uniTax, $rate, true, $deltaRoundingType, $round, $item);
        $price = $priceInclTax - $uniTax;

        //Handle discount
        $discountTaxCompensationAmount = 0;
        $discountAmount = $item->getDiscountAmount();
        if ($applyTaxAfterDiscount) {
            $unitDiscountAmount = $discountAmount / $quantity;
            $taxableAmount = max($priceInclTax - $unitDiscountAmount, 0);
            $unitTaxAfterDiscount = $this->calculationTool->calcTaxAmount(
                $taxableAmount,
                $rate,
                true,
                false
            );
            $unitTaxAfterDiscount = $this->roundAmount(
                $unitTaxAfterDiscount,
                $rate,
                true,
                self::KEY_REGULAR_DELTA_ROUNDING,
                $round,
                $item
            );

            // Set discount tax compensation
            $unitDiscountTaxCompensationAmount = $uniTax - $unitTaxAfterDiscount;
            $discountTaxCompensationAmount = $unitDiscountTaxCompensationAmount * $quantity;
            $uniTax = $unitTaxAfterDiscount;
        }
        $rowTax = $uniTax * $quantity;

        // Calculate applied taxes
        /** @var  \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes */
        $appliedRates = $this->calculationTool->getAppliedRates($taxRateRequest);
        $appliedTaxes = $this->getAppliedTaxes($rowTax, $rate, $appliedRates);

        $absAmount = $item->getAbsoluteAmount();
        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode($item->getCode())
            ->setType($item->getType())
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($price * $quantity + $absAmount)
            ->setRowTotalInclTax($priceInclTax * $quantity + $absInclAmount)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setAssociatedItemCode($item->getAssociatedItemCode())
            ->setTaxPercent($rate)
            ->setAppliedTaxes($appliedTaxes);
    }

    /**
     * @param QuoteDetailsItemInterface $item
     * @param int $quantity
     * @param bool $round
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface|DefaultUnitBaseCalculator
     */
    protected function calculateWithTaxNotInPrice(QuoteDetailsItemInterface $item, $quantity, $round = true)
    {
        if ($this->moduleConfig->isModuleEnable()) {
            return  $this->calculateTaxNotInPriceWhenModuleEnable($item, $quantity, $round);
        }
        return parent::calculateWithTaxNotInPrice($item, $quantity, $round);
    }

    /**
     * @param QuoteDetailsItemInterface $item
     * @param int $quantity
     * @param bool $round
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface|DefaultUnitBaseCalculator
     */
    protected function calculateTaxNotInPriceWhenModuleEnable($item, $quantity, $round)
    {
        $taxRateRequest = $this->getAddressRateRequest()->setProductClassId(
            $this->taxClassManagement->getTaxClassId($item->getTaxClassKey())
        );
        $rate = $this->calculationTool->getRate($taxRateRequest);
        $appliedRates = $this->calculationTool->getAppliedRates($taxRateRequest);

        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($this->storeId);
        $discountAmount = $item->getDiscountAmount();
        $discountTaxCompensationAmount = 0;

        // Calculate $price
        $price = $this->calculationTool->round($item->getUnitPrice());
        $unitTaxes = [];
        $unitTaxesBeforeDiscount = [];
        $appliedTaxes = [];
        //Apply each tax rate separately
        foreach ($appliedRates as $appliedRate) {
            $taxId = $appliedRate['id'];
            $taxRate = $appliedRate['percent'];
            $unitTaxPerRate = $this->calculationTool->calcTaxAmount($price, $taxRate, false, false);
            $deltaRoundingType = self::KEY_REGULAR_DELTA_ROUNDING;
            if ($applyTaxAfterDiscount) {
                $deltaRoundingType = self::KEY_TAX_BEFORE_DISCOUNT_DELTA_ROUNDING;
            }
            $unitTaxPerRate = $this->roundAmount($unitTaxPerRate, $taxId, false, $deltaRoundingType, $round, $item);
            $unitTaxAfterDiscount = $unitTaxPerRate;

            //Handle discount
            if ($applyTaxAfterDiscount) {
                $unitDiscountAmount = $discountAmount / $quantity;
                $taxableAmount = max($price - $unitDiscountAmount, 0);
                $unitTaxAfterDiscount = $this->calculationTool->calcTaxAmount(
                    $taxableAmount,
                    $taxRate,
                    false,
                    false
                );
                $unitTaxAfterDiscount = $this->roundAmount(
                    $unitTaxAfterDiscount,
                    $taxId,
                    false,
                    self::KEY_REGULAR_DELTA_ROUNDING,
                    $round,
                    $item
                );
            }
            $appliedTaxes[$taxId] = $this->getAppliedTax(
                $unitTaxAfterDiscount * $quantity,
                $appliedRate
            );

            $unitTaxes[] = $unitTaxAfterDiscount;
            $unitTaxesBeforeDiscount[] = $unitTaxPerRate;
        }
        $unitTax = array_sum($unitTaxes);
        $unitTaxBeforeDiscount = array_sum($unitTaxesBeforeDiscount);

        $rowTax = $unitTax * $quantity;
        $priceInclTax = $price + $unitTaxBeforeDiscount;

        $absAmount = $item->getAbsoluteAmount();
        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode($item->getCode())
            ->setType($item->getType())
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($price * $quantity + $absAmount)
            ->setRowTotalInclTax($priceInclTax * $quantity + $absAmount)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setAssociatedItemCode($item->getAssociatedItemCode())
            ->setTaxPercent($rate)
            ->setAppliedTaxes($appliedTaxes);
    }
}
