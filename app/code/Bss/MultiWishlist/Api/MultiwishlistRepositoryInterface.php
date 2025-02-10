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
namespace Bss\MultiWishlist\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Interface MultiwishlistRepositoryInterface
 *
 * @package Bss\MultiWishlist\Api
 * @api
 */
interface MultiwishlistRepositoryInterface
{
    /**
     * Get wishlist from id
     *
     * @param int $wishlistId
     * @return \Bss\MultiWishlist\Api\Data\MultiwishlistInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($wishlistId);

    /**
     * Get wishlist
     *
     * @param int $wishlistId
     * @param int $storeId
     * @return mixed
     */
    public function get($wishlistId, $storeId = null);

    /**
     * Save bss multiwishlist
     *
     * @param \Bss\MultiWishlist\Api\Data\MultiwishlistInterface $multiwishlist
     * @return \Bss\MultiWishlist\Api\Data\MultiwishlistInterface
     */
    public function save($multiwishlist);

    /**
     * Delete Bss Multi wishlist
     *
     * @param int $multiWishlistId
     * @return mixed
     * @throws CouldNotSaveException
     */
    public function deleteByMultiWishlistId($multiWishlistId);

    /**
     * Get list bss multi wishlist
     *
     * @param SearchCriteriaInterface $criteria
     * @return \Bss\MultiWishlist\Api\Data\MultiwishlistSearchResultsInterface|\Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Get list bss multi wishlist by customer id
     *
     * @param int $customerId
     * @return \Bss\MultiWishlist\Api\Data\MultiwishlistInterface[]|\Magento\Framework\Api\ExtensibleDataInterface[]
     */
    public function getListByCustomerId($customerId);

    /**
     * Add products to wishlists
     *
     * @param int $wishListId
     * @param mixed $wishlistItems
     * @return \Magento\Framework\Phrase
     */
    public function addProductsToWishList($wishListId, $wishlistItems);

    /**
     * Delete products of wishlists
     *
     * @param int $customerId
     * @param mixed $wishlistItemIds
     * @return \Magento\Framework\Phrase
     */
    public function deleteProductsOfWishList($customerId, $wishlistItemIds);

    /**
     * Get information all product in wishList by wishListId
     *
     * @param int $multiWishListId
     * @param int $customerId
     * @return \Magento\Catalog\Api\Data\ProductInterface[]|\Bss\MultiWishlist\Api\Data\MultiwishlistInterface[]
     */
    public function getListProductByWishListAndCustomerId($customerId, $multiWishListId);

    /**
     * Get list product in all wishlist of customer
     *
     * @param int|null $customerId
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getListProductUserToken($customerId = null);
}
