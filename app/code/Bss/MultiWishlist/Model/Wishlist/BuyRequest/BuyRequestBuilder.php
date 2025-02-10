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
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;

class BuyRequestBuilder
{
    /**
     * @var BuyRequestDataProviderInterface[]
     */
    private $providers;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * Constructor
     *
     * @param DataObjectFactory $dataObjectFactory
     * @param array $providers
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        array $providers = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->providers = $providers;
    }

    /**
     * Build product buy request for adding to wishlist
     *
     * @param WishlistItem $wishlistItemData
     * @param int|null $productId
     *
     * @return DataObject
     */
    public function build(WishlistItem $wishlistItemData, ?int $productId = null): DataObject
    {
        $requestData = [
            [
                'qty' => $wishlistItemData->getQuantity(),
            ]
        ];

        foreach ($this->providers as $provider) {
            $requestData[] = $provider->execute($wishlistItemData, $productId);
        }

        return $this->dataObjectFactory->create(['data' => array_merge(...$requestData)]);
    }
}
