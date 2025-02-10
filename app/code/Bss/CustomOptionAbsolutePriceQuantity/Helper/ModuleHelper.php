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

use Bss\CustomOptionAbsolutePriceQuantity\Plugin\PriceType;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableRepository;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\StoreManagerInterface;

class ModuleHelper
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var PricingHelper
     */
    protected $priceHelper;

    /**
     * @var ConfigurableRepository
     */
    protected $configurableRepository;

    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var TierPriceOptionHelper
     */
    protected $tierPriceOptionHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ModuleHelper constructor.
     *
     * @param PriceCurrencyInterface $priceCurrency
     * @param ProductRepository $productRepository
     * @param PricingHelper $priceHelper
     * @param ConfigurableRepository $configurableRepository
     * @param ModuleConfig $moduleConfig
     * @param MessageManager $messageManager
     * @param TierPriceOptionHelper $tierPriceOptionHelper
     * @param Json $json
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        ProductRepository $productRepository,
        PricingHelper $priceHelper,
        ConfigurableRepository $configurableRepository,
        ModuleConfig $moduleConfig,
        MessageManager $messageManager,
        TierPriceOptionHelper $tierPriceOptionHelper,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        $this->priceHelper = $priceHelper;
        $this->configurableRepository = $configurableRepository;
        $this->moduleConfig = $moduleConfig;
        $this->messageManager = $messageManager;
        $this->tierPriceOptionHelper = $tierPriceOptionHelper;
        $this->json = $json;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * @param AbstractItem $item
     * @param bool $useBaseCurrency
     * @return array
     */
    public function getCoapData(AbstractItem $item, $useBaseCurrency)
    {
        $priceOptionDefault = 0;
        try {
            $infoBuyRequest = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            $postData = $infoBuyRequest["info_buyRequest"];
            $product = $this->productRepository->getById($item->getProduct()->getEntityId());
            // Set custom option to empty array to fix error with grouped product (when getFinalPrice)
            $productCustomOptions = $product->getCustomOptions();
            $product->setCustomOptions([]);
            $optionOder = $this->returnOrderOptionsInfo($postData);
            $orderOptionsInfo = $this->returnCheckOrderOptionsInfo($optionOder);
            $orderOptionsQty = $this->returnOrderOptionsQty($postData);
            $qty = $item->getQty();
            if ($useBaseCurrency) {
                $originalPrice = $item->getBaseTaxCalculationPrice();
                $basePrice = $product->getFinalPrice($qty);
            } else {
                $originalPrice = $item->getTaxCalculationPrice();
                $basePrice = $this->priceHelper->currency($product->getFinalPrice($qty), false, false);
            }
            // Fallback custom options
            $product->setCustomOptions($productCustomOptions);
            $absoluteAmount = 0;
            foreach ($product->getOptions() as $option) {
                $optionId = $option->getOptionId();

                if ($optionId !== null && $orderOptionsInfo !== null && array_key_exists($optionId, $orderOptionsInfo)) {
                    try {
                        $optionQty = $option->getBssCoapQty() ? $orderOptionsQty[$optionId] : 1;
                    } catch (\Exception $e) {
                        $optionQty = 1;
                        $this->messageManager->addExceptionMessage(
                            $e,
                            __('There are some update to the product %1. Please modify your cart.', $product->getName())
                        );
                    }

                    $tierPriceProduct = $product->getTierPrice() ?: [];
                    // Price after calculate tier price
                    $lastPrice = $product->getFinalPrice();
                    foreach ($tierPriceProduct as $tierPrice) {
                        if ($qty >= $tierPrice['price_qty']
                            && $lastPrice > $tierPrice['price']
                        ) {
                            $lastPrice = $tierPrice['price'];
                        }
                    }

                    if (!$this->isSelectType($option->getType())) {
                        $coapAmount = $this->getCoapAmount(
                            $option->getPriceType(),
                            $option->getPrice(),
                            $basePrice,
                            $useBaseCurrency
                        );
                        $coapAmount = $this->tierPriceOptionHelper
                            ->addCoapAmountDataOption(
                                $option,
                                $coapAmount,
                                $lastPrice,
                                $qty,
                                $optionQty
                            );
                        $absoluteAmount += $this->tierPriceOptionHelper
                                ->checkIssetParamAbsTier($coapAmount) * $optionQty;
                        $originalPrice -= $coapAmount['absolute'] + $coapAmount['default'];
                        $priceOptionDefault += $this->tierPriceOptionHelper
                                ->checkIssetParamTier($coapAmount) * $optionQty;
                    } else {
                        $paramSelect = [];
                        $paramSelect['option_id'] = $optionId;
                        $paramSelect['option_qty'] = $optionQty;
                        $paramSelect['order_option_info'] = $orderOptionsInfo;
                        $paramSelect['product_base_price'] = $basePrice;
                        $paramSelect['product_final_price'] = $product->getFinalPrice();
                        $paramSelect['last_price'] = $lastPrice;
                        $coapAmountSelect = $this->getCoapAmountSelect(
                            $option,
                            $paramSelect,
                            $useBaseCurrency,
                            $qty
                        );
                        $absoluteAmount += $coapAmountSelect['abs'] * $optionQty;
                        $priceOptionDefault += $coapAmountSelect['optionDefault'] * $optionQty;
                        $originalPrice -= $coapAmountSelect['default'];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            $originalPrice = $this->getOriginalPriceError($useBaseCurrency, $item);
            $absoluteAmount = 0;
        }
        // set unit price has options (except abs)
        $originalPrice += $priceOptionDefault;
        return ['unit_price' => $originalPrice, 'absolute_amount' => $absoluteAmount];
    }

    /**
     * @param array $postData
     * @return array|mixed
     */
    private function returnOrderOptionsInfo($postData)
    {
        if ($postData !== null) {
            return (array_key_exists('options', $postData)) ? $postData['options'] : [];
        }
        return [];
    }

    /**
     * @param array $postData
     * @return array|mixed
     */
    private function returnOrderOptionsQty($postData)
    {
        if ($postData !== null) {
            return (array_key_exists('option_qty', $postData)) ? $postData['option_qty'] : [];
        }
        return [];
    }

    /**
     * @param array $optionData
     * @return array
     */
    private function returnCheckOrderOptionsInfo($optionData)
    {
        $data = $optionData;
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $item) {
                        if ($item == "") {
                            unset($data[$key]);
                            break;
                        }
                    }
                } else {
                    if ($value == "") {
                        unset($data[$key]);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * @param bool $useBaseCurrency
     * @param mixed $item
     * @return mixed
     */
    protected function getOriginalPriceError($useBaseCurrency, $item)
    {
        if ($useBaseCurrency) {
            return $item->getBaseTaxCalculationPrice();
        } else {
            return $item->getTaxCalculationPrice();
        }
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option
     * @param float $basePrice
     * @param array $orderOptionsInfo
     * @param int $optionId
     * @param float $qty
     * @param bool $useBaseCurrency
     * @return array
     */
    public function getCoapAmountSelect($option, $paramSelect, $useBaseCurrency, $qty)
    {
        $result = ['abs' => 0, 'optionDefault' => 0, 'default' => 0];
        if ($this->isSingleSelect($option->getType())) {
            foreach ($option->getValues() as $value) {
                if ($value->getOptionTypeId() == $paramSelect['order_option_info'][$paramSelect['option_id']]) {
                    $coapAmount = $this->getCoapAmount(
                        $value->getPriceType(),
                        $value->getPrice(),
                        $paramSelect['product_base_price'],
                        $useBaseCurrency
                    );
                    //add tier data option to coap amount
                    $coapAmount = $this->tierPriceOptionHelper
                        ->addCoapAmountDataOptionValue(
                            $value,
                            $coapAmount,
                            $paramSelect['last_price'],
                            $qty,
                            $paramSelect['option_qty']
                        );
                    $result['abs'] += $this->tierPriceOptionHelper
                        ->checkIssetParamAbsTier($coapAmount);
                    $result['optionDefault'] += $this->tierPriceOptionHelper
                        ->checkIssetParamTier($coapAmount);
                    $result['default'] += $coapAmount['absolute'] + $coapAmount['default'];
                    break;
                }
            }
        } else {
            foreach ($option->getValues() as $value) {
                if ($this->checkOptionValueSelected($paramSelect, $value->getOptionTypeId())) {
                    $coapAmount = $this->getCoapAmount(
                        $value->getPriceType(),
                        $value->getPrice(),
                        $paramSelect['product_base_price'],
                        $useBaseCurrency
                    );
                    //add tier data option to coap amount
                    $coapAmount = $this->tierPriceOptionHelper
                        ->addCoapAmountDataOptionValue(
                            $value,
                            $coapAmount,
                            $paramSelect['last_price'],
                            $qty,
                            $paramSelect['option_qty']
                        );
                    $result['abs'] += $this->tierPriceOptionHelper
                        ->checkIssetParamAbsTier($coapAmount);
                    $result['optionDefault'] += $this->tierPriceOptionHelper
                        ->checkIssetParamTier($coapAmount);
                    $result['default'] += $coapAmount['absolute'] + $coapAmount['default'];
                }
            }
        }
        return $result;
    }

    /**
     * @param array $paramSelect
     * @param int $valueId
     * @return bool
     */
    protected function checkOptionValueSelected($paramSelect, $valueId)
    {
        if (is_array($paramSelect['order_option_info'][$paramSelect['option_id']])) {
            if (in_array(
                $valueId,
                $paramSelect['order_option_info'][$paramSelect['option_id']]
            )) {
                return true;
            }
            return false;
        }
        if ($paramSelect['order_option_info'][$paramSelect['option_id']] != "") {
            $valueSelected = explode(",", $paramSelect['order_option_info'][$paramSelect['option_id']]);
            if (in_array(
                $valueId,
                $valueSelected
            )) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $type
     * @param float $value
     * @param float $base
     * @param bool $useBaseCurrency
     * @return array
     */
    public function getCoapAmount($type, $value, $base, $useBaseCurrency)
    {
        if ($useBaseCurrency) {
            $amount = $value;
        } else {
            $amount = $this->priceHelper->currency($value, false, false);
        }
        $result = ['absolute' => 0, 'default' => 0];
        if ($type === PriceType::ABSOLUTE_PRICETYPE) {
            $result['absolute'] = $amount;
        } elseif ($type === 'fixed') {
            $result['default'] = $amount;
        } elseif ($type === 'percent') {
            $result['default'] = $value * $base / 100;
        }
        return $result;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterface[] $productOptions
     * @param array $orderOptions
     * @param float $basePrice
     * @param float $taxRate
     * @return array
     */
    public function getOptionData($productOptions, $orderOptions, $basePrice, $taxRate)
    {
        $options = [];
        foreach ($productOptions as $option) {
            $options[$option->getOptionId()] = $option->getData();
            if (!$this->isSelectType($option->getType())) {
                $this->getPriceString(
                    $option->getPrice(),
                    $option->getPriceType(),
                    $basePrice,
                    $taxRate,
                    $options[$option->getOptionId()]
                );
                $this->getTierPriceString(
                    $options[$option->getOptionId()]['tierPriceProduct'],
                    $taxRate,
                    $options[$option->getOptionId()]
                );
            } else {
                foreach ($option->getValues() as $value) {
                    $options[$option->getOptionId()]['values'][$value->getOptionTypeId()] = $value->getData();
                    $this->getPriceString(
                        $value->getPrice(),
                        $value->getPriceType(),
                        $basePrice,
                        $taxRate,
                        $options[$option->getOptionId()]['values'][$value->getOptionTypeId()]
                    );
                    $this->getTierPriceString(
                        $options[$option->getOptionId()]['values'][$value->getOptionTypeId()]['tierPriceProduct'],
                        $taxRate,
                        $options[$option->getOptionId()]['values'][$value->getOptionTypeId()]
                    );
                }
            }
        }
        if (!empty($orderOptions['options'])) {
            foreach ($orderOptions['options'] as $option) {
                if ($this->isSelectType($option['option_type'])) {
                    $options[$option['option_id']]['selected_value'] = $option['option_value'];
                }
            }
        }
        return $options;
    }

    /**
     * Get price string and get product tier price.
     *
     * @param float $amount
     * @param string $type
     * @param float $basePrice
     * @param float $taxRate
     * @param array $options
     * @return void
     */
    public function getPriceString($amount, $type, $basePrice, $taxRate, &$options)
    {
        $isPriceInclTax = $this->moduleConfig->isPriceInclTax();
        if ($type === PriceType::PERCENT_PRICETYPE) {
            $amount = $amount * $basePrice / 100;
        }
        $taxAmount = $this->calcTaxAmount(
            $amount,
            $taxRate,
            $isPriceInclTax
        );
        if ($isPriceInclTax) {
            if ($this->moduleConfig->displayCartPriceExclTax()) {
                $amount = $amount - $taxAmount;
            }
        } else {
            if ($this->moduleConfig->displayCartPriceInclTax()) {
                $amount = $amount + $taxAmount;
            }
        }

        $options['price'] = $this->priceHelper->currency($amount, true, false);
        $options['tierPriceProduct'] = $amount;
        return;
    }

    /**
     * Get tier price string
     *
     * @param float $basePriceCO
     * @param float $taxRate
     * @param array $options
     * @return void
     */
    public function getTierPriceString($basePriceCO, $taxRate, &$options)
    {
        $tierPrice = !empty($options['bss_tier_price_option']) ? $this->json->unserialize((string)$options['bss_tier_price_option']) : [];

        foreach ($tierPrice as $tp) {
            $isPriceInclTax = $this->moduleConfig->isPriceInclTax();
            $amount = $tp['price'];
            if ($tp['price-type'] === PriceType::PERCENT_PRICETYPE) {
                $amount = $basePriceCO - ($amount * $basePriceCO / 100);
            }
            $taxAmount = $this->calcTaxAmount(
                $amount,
                $taxRate,
                $isPriceInclTax
            );
            if ($isPriceInclTax) {
                if ($this->moduleConfig->displayCartPriceExclTax()) {
                    $amount = $amount - $taxAmount;
                }
            } else {
                if ($this->moduleConfig->displayCartPriceInclTax()) {
                    $amount = $amount + $taxAmount;
                }
            }

            $options['tier_price_string'][$tp['price_qty']]['website_id'] = $tp['website_id'];
            $options['tier_price_string'][$tp['price_qty']]['cust_group'] = $tp['cust_group'];
            $options['tier_price_string'][$tp['price_qty']]['tp_string'] = $this->priceHelper->currency($amount, true, false);
        }
        return;
    }

    /**
     * @param array $result
     * @param array $orderOptions
     * @param \Magento\Catalog\Model\Product $product
     * @param float $taxRate
     * @param float $qty
     * @return array
     */
    public function addCoapInfo($result, $orderOptions, $product, $taxRate, $qty)
    {
        try {
            $finalPrice = $this->productRepository->getById($product->getEntityId())->getFinalPrice($qty);

            $options = $this->getOptionData(
                $product->getOptions(),
                $orderOptions,
                $finalPrice,
                $taxRate
            );
            $result = $this->setResultData($result, $options, $orderOptions, $qty);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
        }
        return $result;
    }

    /**
     * Set result data.
     *
     * @param array $result
     * @param mixed $options
     * @param array $orderOptions
     * @param float $qty
     * @return array
     * @throws NoSuchEntityException
     */
    protected function setResultData($result, $options, $orderOptions, $qty = 1)
    {
        $customerGroupId = $this->customerSession->isLoggedIn()
            ? $this->customerSession->getCustomer()->getGroupId()
            : Group::NOT_LOGGED_IN_ID;
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        foreach ($result as $key => $optResult) {
            $qtyString = $optionQty = '';
            $optionId = $optResult["option_id"];
            if (array_key_exists('bss_coap_qty', $options[$optionId]) &&
                array_key_exists('option_qty', $orderOptions['info_buyRequest']) &&
                $options[$optionId]['bss_coap_qty'] == '1' &&
                $options[$optionId] !== null && $orderOptions['info_buyRequest'] !== null
            ) {
                $optionQty = $orderOptions['info_buyRequest']['option_qty'][$optionId];
                $qtyString = ' x ' . (int)$optionQty;
            }
            $priceCheckTP = (float)$optionQty * (float)$qty;

            if (!$this->isSelectType($options[$optionId]['type'])) {
                $priceString = isset($options[$optionId])
                    ? $this->getCoapOptionDetail($options[$optionId], $priceCheckTP, $customerGroupId, $websiteId)
                    : '';
                $result[$key]['label'] .= $qtyString;
                $result[$key]['value'] .= $priceString;
                $result[$key]['print_value'] .= $priceString;
            } else {
                if ($this->isSingleSelect($options[$optionId]['type'])) {
                    $valueId = $options[$optionId]['selected_value'];
                    $priceString = isset($options[$optionId]['values'][$valueId])
                        ? $this->getCoapOptionDetail($options[$optionId]['values'][$valueId], $priceCheckTP, $customerGroupId, $websiteId)
                        : '';
                    $result[$key]['label'] .= $qtyString;
                    $result[$key]['value'] .= $priceString;
                    $result[$key]['print_value'] .= $priceString;
                } else {
                    $valueId = explode(',', $options[$optionId]['selected_value'] ?? '');
                    $result[$key]['value'] = [];
                    $result[$key]['print_value'] = [];
                    foreach ($valueId as $value) {
                        $priceString = isset($options[$optionId]['values'][$value])
                            ? $this->getCoapOptionDetail($options[$optionId]['values'][$value], $priceCheckTP, $customerGroupId, $websiteId)
                            : '';
                        $result[$key]['value'][] = $options[$optionId]['values'][$value]['title'] . $priceString;
                        $result[$key]['print_value'][] =
                            $options[$optionId]['values'][$value]['title'] . $priceString;
                    }
                    $result[$key]['label'] .= $qtyString;
                    $result[$key]['value'] = implode(', ', $result[$key]['value']);
                    $result[$key]['print_value'] = implode(', ', $result[$key]['print_value']);
                }
            }
            if ($optionQty) {
                $result[$key]['option_qty'] = $optionQty;
            }
        }
        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string|null
     */
    public function getJsonPricesData($product)
    {
        if ($product->getTypeId() === 'configurable') {
            $result = [];
            try {
                $childrens = $this->configurableRepository->getChildrenIds($product->getEntityId());
                $parentAttribute = $this->configurableRepository->getConfigurableAttributes($product);
                foreach ($childrens[0] as $childId) {
                    $child = $this->productRepository->getById($childId);
                    $childPrice['final'] = $child->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
                    $childPrice['base'] = $child->getPriceInfo()
                        ->getPrice('final_price')
                        ->getAmount()
                        ->getBaseAmount();
                    $key = '';
                    foreach ($parentAttribute as $attrKey => $attrValue) {
                        $attrCode = $attrValue->getProductAttribute()->getAttributeCode();
                        $attrKey = $child->getData($attrCode);
                        $key .= $attrKey . '_';
                    }
                    $result['child'][$key] = $childPrice;
                }
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            }
            return $this->json->serialize($result);
        }
        return null;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getJsonTierPricesData($product)
    {
        $result = [];
        $result['type'] = $product->getTypeId();
        if ($product->getTypeId() === 'simple') {
            $result['price'] = $this->getTierPriceData($product);
        } elseif ($product->getTypeId() === 'configurable') {
            $result['price'] = $this->getConfigurableTierPrice($product);
        } elseif ($product->getTypeId() === 'bundle') {
            $result['price'] = $this->getBundleTierPrices($product);
        }
        return $this->json->serialize($result);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getConfigurableTierPrice($product)
    {
        $result = [];
        try {
            $childrens = $this->configurableRepository->getChildrenIds($product->getEntityId());
            $parentAttribute = $this->configurableRepository->getConfigurableAttributes($product);
            foreach ($childrens[0] as $childId) {
                $child = $this->productRepository->getById($childId);
                $childTierPrice = $this->getTierPriceData($child);
                $key = '';
                foreach ($parentAttribute as $attrKey => $attrValue) {
                    $attrCode = $attrValue->getProductAttribute()->getAttributeCode();
                    $attrKey = $child->getData($attrCode);
                    $key .= $attrKey . '_';
                }
                $result['child'][$key] = $childTierPrice;
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
        }
        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getTierPriceData($product)
    {
        $result = [];
        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $baseFinalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
        $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
        if (isset($tierPricesList) && !empty($tierPricesList)) {
            foreach ($tierPricesList as $tier) {
                $tierData = [];
                $tierData['qty'] = $tier['price_qty'];
                $tierData['final'] = $tier['price']->getValue();
                $tierData['base'] = $tier['price']->getBaseAmount();
                $tierData['final_discount'] = $tierData['final'] - $finalPrice;
                $tierData['base_discount'] = $tierData['base'] - $baseFinalPrice;
                $tierData['percent'] = (1 - $tierData['base']/$baseFinalPrice) * 100;
                $result[] = $tierData;
            }
        }
        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getBundleTierPrices($product)
    {
        $customerId = $this->moduleConfig->getCustomerGroupId();
        $result = [];
        $finalResult = [];
        foreach ($product->getTierPrices() as $price) {
            if (($price->getCustomerGroupId() == '32000' || $price->getCustomerGroupId() == $customerId)) {
                if (array_key_exists($price->getQty(), $finalResult)) {
                    $finalResult[$price->getQty()]['value'] = max(
                        $finalResult[$price->getQty()]['value'],
                        $price->getValue()
                    );
                } else {
                    $finalResult[$price->getQty()]['qty'] = $price->getQty();
                    $finalResult[$price->getQty()]['value'] = $price->getValue();
                }
            }
        }
        foreach ($finalResult as $value) {
            if ($this->moduleConfig->getMagentoVersion() >= '2.2.0') {
                $basePrice = $product->getPrice();
                $result[] = ['qty' => $value['qty'], 'tier_percent' => 100 * $value['value']/$basePrice];
            } else {
                $result[] = ['qty' => $value['qty'], 'tier_percent' => (100 - $value['value'])];
            }
        }
        return $result;
    }

    /**
     * Get coap option detail
     *
     * @param array $data
     * @param float $qtyCheck
     * @param int $customerGroupId
     * @param int $websiteId
     * @return string
     */
    public function getCoapOptionDetail($data, $qtyCheck, $customerGroupId, $websiteId)
    {
        $tierPrice = $data['tier_price_string'] ?? [];
        krsort($tierPrice); // sort with qty tier price
        $priceString = $data['price'];

        foreach ($tierPrice as $qtyTierPrice => $item) {
            $allGroupApply = explode(',', $item['cust_group']);
            if ($qtyCheck >= (int)$qtyTierPrice
                && (in_array($customerGroupId, $allGroupApply) || in_array(Group::CUST_GROUP_ALL, $allGroupApply))
                && $item['website_id'] = $websiteId
            ) {
                $priceString = $item['tp_string'];
                break;
            }
        }

        if ($data['price_type'] === PriceType::ABSOLUTE_PRICETYPE) {
            return ' (' . $priceString . ' - ' . __('absolute price') . ')';
        } else {
            return ' (' . $priceString . ')';
        }
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isSelectType($type)
    {
        return $type === 'drop_down' || $type === 'radio' || $type === 'checkbox' || $type === 'multiple';
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isSingleSelect($type)
    {
        return $type === 'drop_down' || $type === 'radio';
    }

    /**
     * @param float $price
     * @param float $taxRate
     * @param bool $priceIncludeTax
     * @return float
     */
    public function calcTaxAmount($price, $taxRate, $priceIncludeTax = false)
    {
        $taxRate = $taxRate / 100;

        if ($priceIncludeTax) {
            $amount = $price * (1 - 1 / (1 + $taxRate));
        } else {
            $amount = $price * $taxRate;
        }

        return $amount;
    }

    /**
     * @param mixed $product
     * @return bool|false|string
     */
    public function getOptionTierPrices($product)
    {
        return $this->tierPriceOptionHelper->getOptionTierPrices($product);
    }
}
