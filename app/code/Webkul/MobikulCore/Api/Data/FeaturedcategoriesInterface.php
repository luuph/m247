<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Api\Data;

/**
 * Interface FeaturedcategoriesInterface
 */
interface FeaturedcategoriesInterface
{
    public const ID = "id";
    public const STATUS = "status";
    public const FILENAME = "filename";
    public const STORE_ID = "store_id";
    public const SORT_ORDER = "sort_order";
    public const CATEGORY_ID = "category_id";
    public const UPDATE_TIME = "update_time";
    public const CREATED_TIME = "created_time";

    /**
     * Function getId
     *
     * @return integer
     */
    public function getId();

    /**
     * Function setId
     *
     * @param integer $id id
     */
    public function setId($id);

    /**
     * Function getStatus
     *
     * @return integer
     */
    public function getStatus();

    /**
     * Function setStatus
     *
     * @param integer $status status
     */
    public function setStatus($status);

    /**
     * Function getFilename
     *
     * @return string
     */
    public function getFilename();

    /**
     * Function setFilename
     *
     * @param string $filename filename
     */
    public function setFilename($filename);

    /**
     * Function getStoreId
     *
     * @return integer
     */
    public function getStoreId();

    /**
     * Function setStoreId
     *
     * @param integer $storeId storeId
     */
    public function setStoreId($storeId);

    /**
     * Function getSortOrder
     *
     * @return integer
     */
    public function getSortOrder();

    /**
     * Function setSortOrder
     *
     * @param integer $sortOrder sortOrder
     */
    public function setSortOrder($sortOrder);

    /**
     * Function getCategoryId
     *
     * @return integer
     */
    public function getCategoryId();

    /**
     * Function setCategoryId
     *
     * @param integer $categoryId categoryId
     */
    public function setCategoryId($categoryId);

    /**
     * Function getUpdateTime
     *
     * @return string
     */
    public function getUpdateTime();

    /**
     * Function setUpdateTime
     *
     * @param string $updatedAt updatedAt
     */
    public function setUpdateTime($updatedAt);

    /**
     * Function getCreatedTime
     *
     * @return string
     */
    public function getCreatedTime();

    /**
     * Function setCreatedTime
     *
     * @param string $createdAt createdAt
     */
    public function setCreatedTime($createdAt);
}
