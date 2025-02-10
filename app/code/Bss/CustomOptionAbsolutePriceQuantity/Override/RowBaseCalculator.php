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
use Magento\Tax\Model\Calculation\RowBaseCalculator as DefaultRowBaseCalculator;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;

class RowBaseCalculator extends DefaultRowBaseCalculator
{
    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Bss\CustomOptionAbsolutePriceQuantity\Model\AggregateCalculator
     */
    protected $aggregateCalculator;

    /**
     * @var \Magento\Framework\ObjectManagerInterface;
     */
    protected $objectManager;

    /**
     * RowBaseCalculator constructor.
     * @param TaxClassManagementInterface $taxClassService
     * @param TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory
     * @param AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory
     * @param AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory
     * @param Calculation $calculationTool
     * @param \Magento\Tax\Model\Config $config
     * @param int $storeId
     * @param ModuleConfig $moduleConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\DataObject $addressRateRequest = null
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->objectManager = $objectManager;
        $this->aggregateCalculator = $this->objectManager->create(
            \Bss\CustomOptionAbsolutePriceQuantity\Model\AggregateCalculator::class,
            ['storeId' => $storeId]
        );
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
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface|DefaultRowBaseCalculator
     */
    protected function calculateWithTaxInPrice(QuoteDetailsItemInterface $item, $quantity, $round = true)
    {
        if ($this->moduleConfig->isModuleEnable()) {
            return $this->aggregateCalculator->calculateTaxPriceWhenModuleEnable(
                $item,
                $quantity,
                $round,
                'row'
            );
        }
        return parent::calculateWithTaxInPrice($item, $quantity, $round);
    }

    /**
     * @param QuoteDetailsItemInterface $item
     * @param int $quantity
     * @param bool $round
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface|DefaultRowBaseCalculator
     */
    protected function calculateWithTaxNotInPrice(QuoteDetailsItemInterface $item, $quantity, $round = true)
    {
        if ($this->moduleConfig->isModuleEnable()) {
            return  $this->aggregateCalculator->calculateTaxNotInPriceWhenModuleEnable(
                $item,
                $quantity,
                $round,
                'row'
            );
        }
        return parent::calculateWithTaxNotInPrice($item, $quantity, $round);
    }
}
