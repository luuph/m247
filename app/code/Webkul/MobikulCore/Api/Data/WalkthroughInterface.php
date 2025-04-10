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
 * Interface WalkthroughInterface
 */
interface WalkthroughInterface
{
    public const ID = "id";
    public const TITLE = "title";
    public const DESCRIPTION = "description";
    public const COLOR_CODE = "color_code";
    public const CREATED_AT = "created_at";
    public const UPDATED_AT = "updated_at";
    public const IMAGE = "image";
    public const SORT_ORDER = "sort_order";
    public const STATUS = "status";

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
     * Function getTitle
     *
     * @return string
     */
    public function getTitle();

    /**
     * Function setTitle
     *
     * @param string $title content
     */
    public function setTitle($title);

    /**
     * Function getDescription
     *
     * @return string
     */
    public function getDescription();

    /**
     * Function setDescription
     *
     * @param string $description content
     */
    public function setDescription($description);

    /**
     * Function getColorCode
     *
     * @return string
     */
    public function getColorCode();

    /**
     * Function setColorCode
     *
     * @param string $colorCode colorCode
     */
    public function setColorCode($colorCode);

    /**
     * Function getImage
     *
     * @return string
     */
    public function getImage();

    /**
     * Function setImage
     *
     * @param string $image image
     */
    public function setImage($image);

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
}
