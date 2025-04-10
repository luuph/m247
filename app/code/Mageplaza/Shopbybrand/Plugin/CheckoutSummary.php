<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Plugin;

use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\ItemFactory;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class CheckoutSummary
 * @package Mageplaza\Shopbybrand\Plugin
 */
class CheckoutSummary
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * CheckoutSummary constructor.
     *
     * @param Data $helperData
     * @param ItemFactory $itemFactory
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Data $helperData,
        ItemFactory $itemFactory,
        ProductRepository $productRepository
    ) {
        $this->helperData        = $helperData;
        $this->itemFactory       = $itemFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @param DefaultConfigProvider $subject
     * @param $result
     *
     * @throws NoSuchEntityException
     */
    public function afterGetConfig(DefaultConfigProvider $subject, $result)
    {
        $items     = $result['totalsData']['items'];
        $itemsData = [];
        foreach ($items as $key => $item) {

            $itemId      = $item['item_id'];
            $currentItem = $this->itemFactory->create()->load($itemId);
            $product     = $this->productRepository->getById(
                $currentItem->getProductId(),
                false,
                $currentItem->getStoreId()
            );
            $brand       = $this->helperData->getProductBrand($currentItem);
            if ($brand) {
                $data                 = $this->helperData->getItemBrandCheckoutData($currentItem);
                $itemsData[$itemId]   = $data;
                $items[$key]['brand'] = $data;
            }
        }

        if ($this->helperData->isOscEnable()) {
            foreach ($result['quoteItemData'] as &$item) {
                if (isset($itemsData[$item['item_id']])) {
                    $item['brand'] = $itemsData[$item['item_id']];
                }
            }
        }
        $result['totalsData']['items'] = $items;

        return $result;
    }
}
