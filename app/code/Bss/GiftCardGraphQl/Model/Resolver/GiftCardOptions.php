<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     Bss_GiftCardGraphQl
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCardGraphQl\Model\Resolver;

use Bss\GiftCard\Api\PatternRepositoryInterface;
use Bss\GiftCard\Helper\Catalog\Product\Configuration as ConfigurationGiftCard;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Bss\GiftCard\Model\TemplateFactory;

/**
 * Class GiftCardOptions
 *
 * @package Bss\GiftCardGraphQl\Model\Resolver
 */
class GiftCardOptions implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    const BSS_GIFT_CARD = 'bss_giftcard';
    const BSS_GIFT_CARD_AMOUNTS = 'bss_gift_card_amounts';
    const BSS_GIFT_CARD_DYNAMIC_PRICE = 'bss_gift_card_dynamic_price';
    const BSS_GIFT_CARD_OPEN_MIN_AMOUNT = 'bss_gift_card_open_min_amount';
    const BSS_GIFT_CARD_OPEN_MAX_AMOUNT = 'bss_gift_card_open_max_amount';
    const BSS_GIFT_CARD_PERCENTAGE_VALUE = 'bss_gift_card_percentage_value';
    const BSS_GIFT_CARD_PERCENTAGE_TYPE = 'bss_gift_card_percentage_type';
    const BSS_GIFT_CARD_CODE_PATTERN = 'bss_gift_card_code_pattern';
    const BSS_GIFT_CARD_MESSAGE = 'bss_gift_card_message';
    const BSS_GIFT_CARD_EXPIRES = 'bss_gift_card_expires';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var PatternRepositoryInterface
     */
    private $patternRepository;

    /**
     * GiftCardOptions constructor.
     *
     * @param PriceCurrencyInterface $priceCurrency
     * @param TemplateFactory $templateFactory
     * @param PatternRepositoryInterface $patternRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        TemplateFactory $templateFactory,
        PatternRepositoryInterface $patternRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        $this->templateFactory = $templateFactory;
        $this->patternRepository = $patternRepository;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $result = [];
        $productId = $value['entity_id'];
        $product = $this->productRepository->getById($productId);
        if ($type = $product->getTypeId() === self::BSS_GIFT_CARD) {
            $result['type'] = $this->getAttributeByProduct($product, self::BSS_GIFT_CARD);
            $result['amount'] = $this->getAmountData($product);
            $result['dynamic_price'] = $this->getDynamicPrice($product);
            $result['template'] = $this->getTemplate($product);
            $result['pattern'] = $this->getPattern($product);
            $result['message'] = $product->getData(self::BSS_GIFT_CARD_MESSAGE);
            $result['expires_at'] = $product->getData(self::BSS_GIFT_CARD_EXPIRES);
        }

        return $result;
    }

    /**
     * Get attribute by product
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Catalog\Api\Data\ProductInterface $product
     * @param string $code
     * @return mixed|null
     */
    private function getAttributeByProduct(\Magento\Catalog\Model\Product $product, $code)
    {
        if (!$product->hasData($code)) {
            $product->getResource()->load($product, $product->getId());
        }
        return $product->getData($code);
    }

    /**
     * Get gift card amount
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     */
    protected function getAmountData($product)
    {
        $amountsData = $product->getData(self::BSS_GIFT_CARD_AMOUNTS);
        $result = [];
        if (!empty($amountsData)) {
            foreach ($amountsData as $amount) {
                $value = $this->priceCurrency->round($amount['value']);
                $result[] = [
                    'id' => (int) $amount['amount_id'],
                    'price' => $this->priceCurrency->convert($amount['price']),
                    'value' => (float) $amount['value'],
                    'label' => $this->priceCurrency->format($value, false),
                    'website' => (int) $amount['website_id']
                ];
            }
        }
        return $result;
    }

    /**
     * Get Dynamic price
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return array
     */
    protected function getDynamicPrice($product)
    {
        $result['enable'] = $product->getData(self::BSS_GIFT_CARD_DYNAMIC_PRICE);
        $result['min_value'] = $this->priceCurrency->round($product->getData(self::BSS_GIFT_CARD_OPEN_MIN_AMOUNT));
        $result['max_value'] = $this->priceCurrency->round($product->getData(self::BSS_GIFT_CARD_OPEN_MAX_AMOUNT));
        $result['percentage_price_type'] = $product->getData(self::BSS_GIFT_CARD_PERCENTAGE_TYPE);
        $result['percentage_price_value'] = $product->getData(self::BSS_GIFT_CARD_PERCENTAGE_VALUE);
        return $result;
    }

    /**
     * Get Gift card template
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getTemplate($product)
    {
        $productId = $product->getId();
        $templateModel = $this->templateFactory->create();
        return $templateModel->loadProductTemplate($productId);
    }

    /**
     * Get pattern linked with product
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getPattern($product)
    {
        $result = [];
        $patternId = $product->getData(self::BSS_GIFT_CARD_CODE_PATTERN);
        $pattern = $this->patternRepository->getPatternById($patternId, true)['pattern_data'];
        if ($pattern) {
            $result['id'] = $pattern['pattern_id'];
            $result['name'] = $pattern['name'];
            $result['pattern'] = $pattern['pattern'];
            $result['qty'] = $pattern['pattern_code_qty'];
            $result['unused'] = $pattern['pattern_code_unused'];
            $result['qty_max'] = $pattern['pattern_code_qty_max'];
            $result['codes'] = $pattern['codes'];
        }
        return $result;
    }
}
