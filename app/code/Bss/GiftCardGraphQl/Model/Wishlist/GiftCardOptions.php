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

use Bss\GiftCard\Helper\Catalog\Product\Configuration as ConfigurationGiftCard;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GiftCardOptions implements ResolverInterface
{
    /**
     * @var ConfigurationGiftCard
     */
    protected $configurationCard;

    /**
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $wishlist;

    /**
     * @var \Bss\GiftCardGraphQl\Model\Cart\CartGiftCardOption
     */
    protected $cartGiftCardOption;

    /**
     * @var array
     */
    protected $wishlistId = [];

    /**
     * Construct.
     *
     * @param ConfigurationGiftCard $configurationCard
     * @param \Magento\Wishlist\Model\Wishlist $wishlist
     * @param \Bss\GiftCardGraphQl\Model\Cart\CartGiftCardOption $cartGiftCardOption
     */
    public function __construct(
        ConfigurationGiftCard $configurationCard,
        \Magento\Wishlist\Model\Wishlist $wishlist,
        \Bss\GiftCardGraphQl\Model\Cart\CartGiftCardOption $cartGiftCardOption
    ) {
        $this->configurationCard = $configurationCard;
        $this->wishlist = $wishlist;
        $this->cartGiftCardOption = $cartGiftCardOption;
    }

    /**
     * Return option gift card in wishlist.
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $result = [];

            $wishlists = $this->wishlist->loadByCustomerId($context->getUserId())->getItemCollection();

            foreach ($wishlists as $wishlist) {
                if (!in_array($wishlist->getWishlistItemId(), $this->wishlistId)) {
                    $item = $wishlist;
                    $this->wishlistId[] = $item->getWishlistItemId();
                    break;
                }
            }

            if (!empty($item)) {
                $options = $this->configurationCard->getGiftCardOptions($item, false);
                $result = $this->cartGiftCardOption->formatResult($options);
            }

            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }
}
