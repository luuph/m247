<?php

namespace WeltPixel\GA4\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;

class WishlistAddToFromCart
{
    /**
     * @var \WeltPixel\GA4\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \WeltPixel\GA4\Helper\Data $helper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductRepositoryInterface $productRepository
 */
    public function __construct(
        \WeltPixel\GA4\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository

    )
    {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Wishlist\Model\Wishlist $subject
     * @param $result
     * @param int|Product $product
     * @param DataObject|array|string|null $buyRequest
     * @param bool $forciblySetQty
     * @return \Magento\Wishlist\Model\Item|string
     * @throws \Magento\Catalog\Model\Product\Exception
     */
    public function afterAddNewItem(
        \Magento\Wishlist\Model\Wishlist $subject,
        $result,
        $product,
        $buyRequest = null,
        $forciblySetQty = false,
        )
    {
        if (!$this->helper->isEnabled()) {
            return $result;
        }

        if ($product instanceof Product) {
            return $result;
        }

        $productId = (int)$product;
        if ($productId && $result) {
            try {
                /** @var Product $product */
                $product = $this->productRepository->getById($productId);
                $this->customerSession->setGA4AddToWishListData($this->helper->addToWishListPushData($product, $buyRequest, null));
            } catch (\Exception $e) {
                return $result;
            }
        }

        return $result;
    }


}
