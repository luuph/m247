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
namespace Bss\CustomOptionAbsolutePriceQuantity\Helper;

use Magento\Catalog\Model\Product\Option;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class TierPriceOptionHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SELECT_TYPE_OPTION = [
        Option::OPTION_TYPE_DROP_DOWN,
        Option::OPTION_TYPE_RADIO,
        Option::OPTION_TYPE_CHECKBOX,
        Option::OPTION_TYPE_MULTIPLE
    ];

    const SELECT_MULTI = [
        Option::OPTION_TYPE_CHECKBOX,
        Option::OPTION_TYPE_MULTIPLE
    ];

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel\TierPriceOption
     */
    protected $resourceTierPriceOption;

    /**
     * @var \Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel\TierPriceOptionValue
     */
    protected $resourceTierPriceOptionValue;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\CalculatorInterface
     */
    protected $calculator;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    protected $taxCalculation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * TierPriceOptionHelper constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel\TierPriceOption $resourceTierPriceOption
     * @param \Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel\TierPriceOptionValue $resourceTierPriceOptionValue
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator
     * @param GroupManagementInterface $groupManagement
     * @param PriceCurrencyInterface $priceCurrency
     * @param ModuleConfig $moduleConfig
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel\TierPriceOption $resourceTierPriceOption,
        \Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel\TierPriceOptionValue $resourceTierPriceOptionValue,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator,
        GroupManagementInterface $groupManagement,
        PriceCurrencyInterface $priceCurrency,
        ModuleConfig $moduleConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculation,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->resourceTierPriceOption = $resourceTierPriceOption;
        $this->resourceTierPriceOptionValue = $resourceTierPriceOptionValue;
        $this->json = $json;
        $this->calculator = $calculator;
        $this->groupManagement = $groupManagement;
        $this->moduleConfig = $moduleConfig;
        $this->taxCalculation = $taxCalculation;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @param mixed $product
     * @param float $priceExcl
     * @return float|int
     */
    public function getPriceInclTax($product, $priceExcl)
    {
        $taxAttribute = $product->getCustomAttribute('tax_class_id');

        if ($taxAttribute
            && $this->moduleConfig->getConfigCatalogPrices() === 0 // If config Catalog Price is Excl. Tax
        ) {
            // First get base price (=price excluding tax)
            $productRateId = $taxAttribute->getValue();
            $rate = $this->taxCalculation->getCalculatedRate(
                $productRateId,
                $this->moduleConfig->getCustomerId(),
                $this->moduleConfig->getStoreId()
            );
            return $priceExcl + ($priceExcl * ($rate / 100));
        }
        return $priceExcl;
    }

    /**
     * @param mixed $product
     * @return float|int
     */
    public function getProductRateTax($product)
    {
        $taxAttribute = $product->getCustomAttribute('tax_class_id');

        if ($taxAttribute
            && $this->moduleConfig->getConfigCatalogPrices() === 0 // If config Catalog Price is Excl. Tax
        ) {
            // First get base price (=price excluding tax)
            $productRateId = $taxAttribute->getValue();
            $rate = $this->taxCalculation->getCalculatedRate(
                $productRateId,
                $this->moduleConfig->getCustomerId(),
                $this->moduleConfig->getStoreId()
            );
            return (float)($rate / 100);
        }
        return 0;
    }

    /**
     * @param mixed $product
     * @return bool|false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptionTierPrices($product)
    {
        $data = [];
        $productPice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $productBasePrice = $product->getPrice();
        if ($product->getTypeInstance()->hasOptions($product)) {
            $data = $this->getTierPriceOption($product, $product->getOptions(), $productPice, $productBasePrice);
        }
        return $this->json->serialize($data);
    }

    /**
     * @param mixed $options
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTierPriceOption($product, $options, $productPice, $productBasePrice)
    {
        $data = [];
        foreach ($options as $option) {
            $this->addDataToTierPriceOption($product, $data, $option, $productPice, $productBasePrice);
        }
        return $data;
    }

    /**
     * @param array $data
     * @param mixed $option
     * @param float $productPice
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addDataToTierPriceOption($product, &$data, $option, $productPice, $productBasePrice = 0)
    {
        if (in_array($option->getType(), self::SELECT_TYPE_OPTION)) {
            foreach ($option->getValues() as $value) {
                $tier = $this->resourceTierPriceOptionValue->getTierPrice($value->getId());
                if ($tier) {
                    $tier = $this->json->unserialize($tier);
                    if (!empty($tier)) {
                        $data[$option->getId()][$value->getId()] = $this->getTierPrice(
                            $tier,
                            $this->setCustomDataVar(
                                $value,
                                $product,
                                $productPice,
                                $productBasePrice,
                                $option
                            )
                        );
                    }
                }
            }
        } else {
            $tier = $this->resourceTierPriceOption->getTierPrice($option->getId());
            if ($tier) {
                $tier = $this->json->unserialize($tier);
                if (!empty($tier)) {
                    $data[$option->getId()][] = $this->getTierPrice(
                        $tier,
                        $this->setCustomDataVar(
                            $option,
                            $product,
                            $productPice,
                            $productBasePrice,
                            null
                        )
                    );
                }
            }
        }
    }

    /**
     * @param mixed $option
     * @param mixed $product
     * @param float $productPice
     * @param float $productBasePrice
     * @param null|mixed $type
     * @return array
     */
    private function setCustomDataVar($option, $product, $productPice, $productBasePrice, $type = null)
    {
        $optionTitle = '';
        if ($type) {
            $optionTitle = in_array($type->getType(), self::SELECT_MULTI) ? $option->getTitle() : '';
        }
        $typePrice = $option->getPriceType();
        $price = $option->getPrice();
        $optionPrice = $this->getOptionPriceInclTax($product, $typePrice, $price, $productPice);
        $optionBasePrice = $this->getOptionPriceBase($typePrice, $price, $productBasePrice);
        $customData = [];
        $customData['option_price'] = $optionPrice;
        $customData['option_base_price'] = $optionBasePrice;
        $customData['option_title'] = $optionTitle;
        $customData['product_rate'] = $this->getProductRateTax($product);
        $customData['parent_price_type'] = $option['price_type'] ?? '';
        return $customData;
    }

    /**
     * @param mixed $product
     * @param string $typePrice
     * @param float $price
     * @param float $productPice
     * @return float|int
     */
    protected function getOptionPriceInclTax($product, $typePrice, $price, $productPice)
    {
        if ($typePrice != 'percent') {
            return (float)$this->getPriceInclTax($product, $price);
        }
        return (float)$productPice / 100 * $price;
    }

    /**
     * @param string $typePrice
     * @param float $price
     * @param float $productBasePrice
     * @return float|int
     */
    protected function getOptionPriceBase($typePrice, $price, $productBasePrice)
    {
        if ($typePrice != 'percent') {
            return (float)$price;
        }
        return (float)$productBasePrice / 100 * $price;
    }

    /**
     * @param mixed $value
     * @param array $coapAmount
     * @param float $productPice
     * @param float|int $qty
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addCoapAmountDataOptionValue($value, $coapAmount, $productPice, $qty, $optionQty)
    {
        $tier = $this->resourceTierPriceOptionValue->getTierPrice($value->getId());
        if ($tier) {
            $tier = $this->json->unserialize($tier);
            if (!empty($tier)) {
                $typePrice = $value->getPriceType();
                $price = $value->getPrice();
                $optionPrice = $typePrice != 'percent' ? (float)$price : (float)$productPice/100  * $price;
                $optionTitle = '';
                $qtyTotal = $qty* $optionQty;
                if ($typePrice == 'abs') {
                    $qtyTotal = $optionQty;
                }
                $customData = [];
                $customData['option_price'] = $optionPrice;
                $customData['option_base_price'] = 0;
                $customData['option_title'] = $optionTitle;
                $customData['product_rate'] = 0;
                $data = $this->getTierPrice($tier, $customData);
                foreach ($data as $datum) {
                    if ($qtyTotal >= $datum['price_qty']) {
                        if ($coapAmount['absolute'] > 0) {
                            $coapAmount['abs_tier'] = $datum['final_tier_price_excl'];
                        } else {
                            $coapAmount['tier'] = $datum['final_tier_price_excl'];
                        }
                    }
                }
            }
        }
        return $coapAmount;
    }

    /**
     * @param mixed $option
     * @param array $coapAmount
     * @param float $productPice
     * @param float|int $qty
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addCoapAmountDataOption($option, $coapAmount, $productPice, $qty, $optionQty)
    {
        $tier = $this->resourceTierPriceOption->getTierPrice($option->getId());
        if ($tier) {
            $tier = $this->json->unserialize($tier);
            if (!empty($tier)) {
                $typePrice = $option->getPriceType();
                $price = $option->getPrice();
                $optionPrice = $typePrice != 'percent' ? (float)$price : (float)$productPice/100  * $price;
                $customData = [];
                $customData['option_price'] = $optionPrice;
                $customData['option_base_price'] = 0;
                $customData['option_title'] = '';
                $customData['product_rate'] = 0;
                $data = $this->getTierPrice($tier, $customData);
                $qtyTotal = $qty* $optionQty;
                if ($typePrice == 'abs') {
                    $qtyTotal = $optionQty;
                }
                foreach ($data as $datum) {
                    if ($qtyTotal >= $datum['price_qty']) {
                        if ($coapAmount['absolute'] > 0) {
                            $coapAmount['abs_tier'] = $datum['final_tier_price_excl'];
                        } else {
                            $coapAmount['tier'] = $datum['final_tier_price_excl'];
                        }
                    }
                }
            }
        }
        return $coapAmount;
    }

    /**
     * @param array $coapAmount
     * @return mixed
     */
    public function checkIssetParamAbsTier($coapAmount)
    {
        if (isset($coapAmount['abs_tier'])) {
            return $coapAmount['abs_tier'];
        }
        return $coapAmount['absolute'];
    }

    /**
     * @param array $coapAmount
     * @return mixed
     */
    public function checkIssetParamTier($coapAmount)
    {
        if (isset($coapAmount['tier'])) {
            return $coapAmount['tier'];
        }
        return $coapAmount['default'];
    }

    /**
     * @param mixed $amount
     * @return string
     */
    public function getFormatedPrice($amount)
    {
        return $this->priceCurrency->format($amount);
    }

    /**
     * @param float $price
     * @param bool $excl
     * @return float
     */
    public function calculatorPrice($price, $excl)
    {
        if ($excl) {
            return round($price['base_final_tier_price'], 2);
        }
        return round($price['final_tier_price'], 2);
    }

    /**
     * @param float $price
     * @param float $optionPrice
     * @param string $priceType
     * @return int
     */
    public function calculatorSavePercent($price, $optionPrice, $priceType)
    {
        if ($priceType != 'percent') {
            return (int)(100 - (float)($price/$optionPrice * 100));
        }
        return (int)($price);
    }

    /**
     * @return array
     */
    public function getTierPrice($priceList, $customData)
    {
        $tierPriceList = $this->filterTierPrices($priceList, $customData);
        return $tierPriceList;
    }

    /**
     * @param array $priceList
     * @param array $customData
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function filterTierPrices(array $priceList, $customData)
    {
        $custmerGroupId = $this->moduleConfig->getCustomerGroupId();
        $qtyCache = [];
        $allCustomersGroupId = $this->groupManagement->getAllCustomersGroup()->getId();
        foreach ($priceList as $priceKey => &$price) {
            $price['optionPrice'] = $customData['option_price'];
            $price['optionBasePrice'] = $customData['option_base_price'];
            $price['optionTitle'] = $customData['option_title'];

            // Calculate tier price in coap-subtotal.js if product has tier price & option has type percent & tier price option has type percent
            if (isset($customData['parent_price_type'])) {
                $currencyCode = $this->priceCurrency->getCurrency()->getData('currency_code');
                $price['currency_rate'] = $this->storeManager->getStore()->getBaseCurrency()->getRate($currencyCode);
                $price['parent_price_type'] = $customData['parent_price_type'];
            }

            $childPrice = $this->returnChildPrice($price, $customData);
            $childPriceExcl = $this->returnChildPriceExclTax($price, $customData);
            $childBasePrice = $this->returnChildBasePrice($price, $customData);
            if ($childPrice >= $customData['option_price']) {
                unset($priceList[$priceKey]);
                continue;
            }
            /* filter price by customer group */
            if ($price['cust_group'] != $custmerGroupId &&
                $price['cust_group'] != $allCustomersGroupId) {
                unset($priceList[$priceKey]);
                continue;
            }

            $price['final_tier_price'] = $this->priceCurrency->convert($childPrice);
            $price['final_tier_price_excl'] = $this->priceCurrency->convert($childPriceExcl);
            $price['base_final_tier_price'] = $this->priceCurrency->convert($childBasePrice);
            /* select a lower price for each quantity */
            if (isset($qtyCache[$price['price_qty']])) {
                $priceQty = $qtyCache[$price['price_qty']];
                if ($this->isFirstPriceBetter($price['final_tier_price'], $priceList[$priceQty]['final_tier_price'])) {
                    unset($priceList[$priceQty]);
                    $qtyCache[$price['price_qty']] = $priceKey;
                } else {
                    unset($priceList[$priceKey]);
                }
            } else {
                $qtyCache[$price['price_qty']] = $priceKey;
            }
        }
        return array_values($priceList);
    }

    /**
     * @param array $price
     * @param array $customData
     * @return float|int
     */
    private function returnChildPrice($price, $customData)
    {
        return $price['price-type'] != 'percent'
            ? (float)$price['price'] + ($price['price'] * $customData['product_rate'])
            : (float)$customData['option_price']/100  * (100-$price['price']);
    }

    /**
     * @param array $price
     * @param array $customData
     * @return float|int
     */
    private function returnChildPriceExclTax($price, $customData)
    {
        return $price['price-type'] != 'percent' ? (float)$price['price']
            : (float)$customData['option_price']/100  * (100-$price['price']);
    }

    /**
     * @param array $price
     * @param array $customData
     * @return float|int
     */
    private function returnChildBasePrice($price, $customData)
    {
        return $price['price-type'] != 'percent' ? (float)$price['price']
            : (float)$customData['option_base_price']/100  * (100-$price['price']);
    }

    /**
     * @param float $firstPrice
     * @param float $secondPrice
     * @return bool
     */
    protected function isFirstPriceBetter($firstPrice, $secondPrice)
    {
        return $firstPrice < $secondPrice;
    }

    /**
     * @return mixed
     */
    public function checkTypeProductPriceDisplay()
    {
        return $this->moduleConfig->checkTypeProductPriceDisplay();
    }
}
