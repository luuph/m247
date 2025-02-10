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
 * @package    Bss_ConfigurableMatrixView
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableMatrixView\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class TierAdvCalcAfterUpdateCart implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Bss\ConfigurableMatrixView\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\Product\Option\ValueFactory
     */
    protected $optionValueFactory;

    /**
     * TierAdvCalcAfterUpdateCart constructor.
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Bss\ConfigurableMatrixView\Helper\Data $helper
     * @param \Magento\Catalog\Model\Product\Option\ValueFactory $optionValueFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Checkout\Model\Cart $cart,
        \Bss\ConfigurableMatrixView\Helper\Data $helper,
        \Magento\Catalog\Model\Product\Option\ValueFactory $optionValueFactory
    ) {
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->helper = $helper;
        $this->optionValueFactory = $optionValueFactory;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->isEnabled() && $this->helper->calculateTierPrice()) {
            $total_qty = [];
            $quote = $this->cart->getQuote();
            $this->getTotalQty($quote, $total_qty);

            foreach ($quote->getAllVisibleItems() as $item) {
                if ($item->getIsSameTierPrice()) {
                    $product = $this->productRepository->getById($item->getProductId());
                    $qty = $total_qty[$item->getProductId()];
                    $childId = $item->getOptionByCode('simple_product')->getProduct()->getId();
                    $child  = $this->productRepository->getById($childId);
                    $totalCustomOptionPrice = $qtyTierPrice = 0;
                    $this->getTotalCustomOptionPrice($product, $item, $totalCustomOptionPrice);

                    $tierPrice = $child->getTierPrice($qty);
                    $dataTierPrice = $child->getTierPrice();
                    $this->getQtyTierPrice($dataTierPrice, $qty, $qtyTierPrice);

                    if (isset($tierPrice) && $tierPrice > 0) {
                        if ($tierPrice < $child->getFinalPrice()) {
                            $item->setCustomPrice(round($tierPrice + $totalCustomOptionPrice, 2));
                            $item->setOriginalCustomPrice(round($tierPrice + $totalCustomOptionPrice, 2));
                            $item->getProduct()->setIsSuperMode(true);
                        }
                        if ($qtyTierPrice > $qty) {
                            $item->setCustomPrice(round($child->getFinalPrice() + $totalCustomOptionPrice, 2));
                            $item->setOriginalCustomPrice(round($child->getFinalPrice() + $totalCustomOptionPrice, 2));
                            $item->getProduct()->setIsSuperMode(true);
                        } else {
                            $item->setCustomPrice(round($tierPrice + $totalCustomOptionPrice, 2));
                            $item->setOriginalCustomPrice(round($tierPrice + $totalCustomOptionPrice, 2));
                            $item->getProduct()->setIsSuperMode(true);
                        }
                    }
                }
            }
            $quote->collectTotals();
        }
    }

    /**
     * @param $item
     * @param $product
     * @param $total_qty
     * @return void
     */
    protected function getQtyTierPrice($dataTierPrice, $qty, &$qtyTierPrice)
    {
        foreach ($dataTierPrice as $tierPrices) {
            if ($qty >= (int)$tierPrices['price_qty']) {
                $qtyTierPrice = (int)$tierPrices['price_qty'];
            }
        }
    }

    /**
     * @param $item
     * @param $product
     * @param $total_qty
     * @return void
     */
    protected function getTotalQty($quote, &$total_qty)
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getIsSameTierPrice()) {
                if (isset($total_qty[$item->getProductId()])) {
                    $total_qty[$item->getProductId()] += (int)$item->getQty();
                } else {
                    $total_qty[$item->getProductId()] = $item->getQty();
                }
            }
        }
    }

    /**
     * @param $option
     * @return int
     */
    protected function getOptionPrice($option)
    {
        $price = 0;
        if ($option->getPriceType() == "fixed") {
            $price = $option->getPrice();
        }
        return $price;
    }

    /**
     * @param $product
     * @param $item
     * @return int|void
     */
    protected function getTotalCustomOptionTypeSelect($option, $customOptionItem, &$totalCustomOptionPrice)
    {
        if ($option->getType() === 'drop_down' || $option->getType() === 'radio') {
            $values = $this->optionValueFactory->create()->getValuesCollection($option);
            foreach ($values as $value) {
                if ($value->getId() == $customOptionItem[$option->getId()]) {
                    $totalCustomOptionPrice += $this->getOptionPrice($value);
                }
            }
        } elseif ($option->getType() === 'checkbox' || $option->getType() === 'multiple') {
            $values = $this->optionValueFactory->create()->getValuesCollection($option);
            foreach ($values as $value) {
                if (in_array($value->getId(), $customOptionItem[$option->getId()])) {
                    $totalCustomOptionPrice += $this->getOptionPrice($value);
                }
            }
        }
    }

    /**
     * @param $item
     * @return array
     */
    protected function getCustomOptionItem($item)
    {
        $customOptionItem = [];
        if (!empty($item->getBuyRequest()->getOptions())) {
            foreach ($item->getBuyRequest()->getOptions() as $code => $option) {
                $customOptionItem[$code] = $option;
            }
        }
        return $customOptionItem;
    }

    /**
     * @param $product
     * @param $item
     * @return int|void
     */
    protected function getTotalCustomOptionPrice($product, $item, &$totalCustomOptionPrice)
    {
        if ($options = $product->getOptions()) {
            $typeSelect = ['drop_down', 'radio', 'checkbox', 'multiple'];
            $customOptionItem = $this->getCustomOptionItem($item);
            foreach ($options as $option) {
                if (!isset($customOptionItem[$option->getId()])) {
                    continue;
                }
                if (in_array($option->getType(), $typeSelect)) {
                    $this->getTotalCustomOptionTypeSelect($option, $customOptionItem, $totalCustomOptionPrice);
                } else {
                    $totalCustomOptionPrice += $this->getOptionPrice($option);
                }
            }
        }
    }
}
