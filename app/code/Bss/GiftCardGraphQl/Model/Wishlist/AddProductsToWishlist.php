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
 * @package     BSS_GiftCardGraphQl
 * @author      Extension Team
 * @copyright   Copyright © 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\GiftCardGraphQl\Model\Wishlist;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItem;
use Magento\Wishlist\Model\Wishlist\Data\WishlistOutput;

/**
 * Adding products to wishlist
 */
class AddProductsToWishlist
{
    /**#@+
     * Error message codes
     */
    private const ERROR_PRODUCT_NOT_FOUND = 'PRODUCT_NOT_FOUND';

    private const ERROR_UNDEFINED = 'UNDEFINED';
    /**#@-*/

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var WishlistResourceModel
     */
    private $wishlistResource;

    /**
     * Construct.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param WishlistResourceModel $wishlistResource
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        WishlistResourceModel $wishlistResource
    ) {
        $this->productRepository = $productRepository;
        $this->wishlistResource = $wishlistResource;
    }

    /**
     * Adding products to wishlist
     *
     * @param Wishlist $wishlist
     * @param array $wishlistItems
     *
     * @return WishlistOutput
     *
     * @throws AlreadyExistsException
     */
    public function execute(Wishlist $wishlist, array $wishlistItems): WishlistOutput
    {
        foreach ($wishlistItems as $wishlistItem) {
            $this->addItemToWishlist($wishlist, $wishlistItem);
        }

        $wishlistOutput = $this->prepareOutput($wishlist);

        if ($wishlist->isObjectNew() || count($wishlistOutput->getErrors()) !== count($wishlistItems)) {
            $this->wishlistResource->save($wishlist);
        }

        return $wishlistOutput;
    }

    /**
     * Add product item to wishlist
     *
     * @param Wishlist $wishlist
     * @param WishlistItem $wishlistItem
     *
     * @return void
     */
    public function addItemToWishlist($wishlist, $wishlistItem)
    {
        $sku = $wishlistItem->getSku();

        try {
            $product = $this->productRepository->get($sku, false, null, true);
        } catch (NoSuchEntityException $e) {
            $this->addError(
                __('Could not find a product with SKU "%sku"', ['sku' => $sku])->render(),
                self::ERROR_PRODUCT_NOT_FOUND
            );

            return;
        }

        try {
            if ((int)$wishlistItem->getQuantity() === 0) {
                throw new LocalizedException(__("The quantity of a wish list item cannot be 0"));
            }

            // Gift card option.
            $options['qty'] = $wishlistItem->getQuantity();
            if (is_array($wishlistItem->getGiftcardOptions())) {
                foreach ($wishlistItem->getGiftcardOptions() as $key => $val) {
                    $options[$key] = $val;
                }
            }
            // Gift card CO.
            if (is_array($wishlistItem->getCustomizableOptions())) {
                foreach ($wishlistItem->getCustomizableOptions() as $item) {
                    $options['options'][$item['id']] = $item['value_string'] ? explode(",", $item['value_string']) : [];
                }
            }

            $result = $wishlist->addNewItem($product, $options);

            if (is_string($result)) {
                $this->addError($result);
            }
        } catch (LocalizedException $exception) {
            $this->addError($exception->getMessage());
        } catch (\Throwable $e) {
            $this->addError(
                __(
                    'Could not add the product with SKU "%sku" to the wishlist:: %message',
                    ['sku' => $sku, 'message' => $e->getMessage()]
                )->render()
            );
        }
    }

    /**
     * Add wishlist line item error
     *
     * @param string $message
     * @param string|null $code
     *
     * @return void
     */
    private function addError(string $message, string $code = null): void
    {
        $this->errors[] = new \Magento\Wishlist\Model\Wishlist\Data\Error(
            $message,
            $code ?? self::ERROR_UNDEFINED
        );
    }

    /**
     * Prepare output
     *
     * @param Wishlist $wishlist
     *
     * @return WishlistOutput
     */
    private function prepareOutput(Wishlist $wishlist): WishlistOutput
    {
        $output = new WishlistOutput($wishlist, $this->errors);
        $this->errors = [];

        return $output;
    }
}
