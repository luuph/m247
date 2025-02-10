<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\MultiWishlist\Model\Wishlist\BuyRequest;

use Bss\MultiWishlist\Model\Wishlist\Data\WishlistItem;

interface BuyRequestDataProviderInterface
{
    /**
     * Provide buy request data from add to wishlist item request
     *
     * @param WishlistItem $wishlistItemData
     * @param int|null $productId
     *
     * @return array
     */
    public function execute(WishlistItem $wishlistItemData, ?int $productId): array;
}
