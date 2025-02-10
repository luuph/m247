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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Plugin\Checkout\CustomerData;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class Cart
 *
 * @package Bss\HidePrice\Plugin\Checkout\CustomerData
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class QuoteExtension
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $quoteSession;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Bss\HidePrice\Helper\CartHidePrice
     */
    protected $cartHidePrice;

    /**
     * @var \Bss\HidePrice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

    /**
     * QuoteExtension constructor.
     * @param \Magento\Checkout\Model\Session $quoteSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Bss\HidePrice\Helper\CartHidePrice $cartHidePrice
     * @param \Bss\HidePrice\Helper\Data $helper
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct(
        \Magento\Checkout\Model\Session $quoteSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Bss\HidePrice\Helper\CartHidePrice $cartHidePrice,
        \Bss\HidePrice\Helper\Data $helper
    ) {
        $this->quoteSession = $quoteSession;
        $this->productRepository = $productRepository;
        $this->cartHidePrice = $cartHidePrice;
        $this->helper = $helper;
    }

    /**
     * @param \Bss\QuoteExtension\CustomerData\QuoteExtension $subject
     * @param $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function afterGetSectionData(\Bss\QuoteExtension\CustomerData\QuoteExtension $subject, $result)
    {
        $items = $this->getQuote()->getAllVisibleItems();
        if (is_array($result['items'])) {
            $itemHidePrice = [];
            $showSubtotal = true;
            foreach ($items as $item) {
                if ($item->getProductType() === Configurable::TYPE_CODE) {
                    $parentProductId = $item->getProductId();
                    $childProductSku = $item->getSku();
                    $canShowPrice = $this->cartHidePrice->canShowPrice($parentProductId, $childProductSku);
                } else {
                    $canShowPrice = $this->cartHidePrice->canShowPrice($item->getProductId(), false);
                }
                if (!$canShowPrice) {
                    $itemHidePrice[] = $item->getId();
                }
            }

            foreach ($result['items'] as $key => $itemAsArray) {
                if (!in_array($result['items'][$key]['item_id'], $itemHidePrice)) {
                    continue;
                }
                if (isset($result['items'][$key]['product_price'])) {
                    $showSubtotal = false;
                    $result['items'][$key]['product_price'] = '';
                }
                if (isset($result['items'][$key]['product_price_value'])) {
                    if (is_array($result['items'][$key]['product_price_value'])) {
                        if (isset($result['items'][$key]['product_price_value']['incl_tax'])) {
                            $result['items'][$key]['product_price_value']['incl_tax'] = 0;
                        }
                        if (isset( $result['items'][$key]['product_price_value']['excl_tax'])) {
                            $result['items'][$key]['product_price_value']['excl_tax'] = 0;
                        }
                    } else {
                        $result['items'][$key]['product_price_value'] = 0;
                    }
                }
            }
            if (!$showSubtotal) {
                $result['subtotalAmount'] = 0;
                $result['subtotal'] = '';
                if (isset($result['subtotal_excl_tax'])) {
                    $result['subtotal_excl_tax'] = '';
                    $result['subtotal_incl_tax'] = '';
                }
            }
        }
        return $result;
    }

    /**
     * @return \Magento\Quote\Model\Quote|null
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->quoteSession->getQuoteExtension();
        }
        return $this->quote;
    }
}
