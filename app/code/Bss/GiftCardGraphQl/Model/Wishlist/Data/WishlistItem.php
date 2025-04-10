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

namespace Bss\GiftCardGraphQl\Model\Wishlist\Data;

class WishlistItem
{
    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var string
     */
    protected $sku;

    /**
     * @var array
     */
    protected $giftcardOptions;

    /**
     * @var array
     */
    protected $customizableOptions;

    /**
     * Construct.
     *
     * @param float $quantity
     * @param string $sku
     * @param array $giftcardOptions
     * @param array $customizableOptions
     */
    public function __construct(
        float $quantity,
        string $sku,
        array $giftcardOptions,
        array $customizableOptions
    ) {
        $this->quantity = $quantity;
        $this->sku = $sku;
        $this->giftcardOptions = $giftcardOptions;
        $this->customizableOptions = $customizableOptions;
    }

    /**
     * Get quantity.
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Get Sku.
     *
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Get array gift card option.
     *
     * @return array
     */
    public function getGiftcardOptions()
    {
        return $this->giftcardOptions;
    }

    /**
     * Get array gift card option.
     *
     * @return array
     */
    public function getCustomizableOptions()
    {
        return $this->customizableOptions;
    }
}
