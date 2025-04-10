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
 * Interface UserImageInterface
 */
interface UserImageInterface
{
    public const ID = "id";
    public const BANNER = "banner";
    public const PROFILE = "profile";
    public const IS_SOCIAL = "is_social";
    public const CREATED_AT = "created_at";
    public const CUSTOMER_ID = "customer_id";

    /**
     * Function getId
     *
     * @return string|number
     */
    public function getId();

    /**
     * Function setId
     *
     * @param integer $id image id
     */
    public function setId($id);

    /**
     * Function getBanner
     *
     * @return string
     */
    public function getBanner();

    /**
     * Function setBanner
     *
     * @param string $banner banner
     */
    public function setBanner($banner);

    /**
     * Function getProfile
     *
     * @return string
     */
    public function getProfile();

    /**
     * Function setProfile
     *
     * @param string $profile profile
     */
    public function setProfile($profile);

    /**
     * Function getIsSocial
     *
     * @return bool
     */
    public function getIsSocial();

    /**
     * Function setIsSocial
     *
     * @param integer|bool $isSocial isSocial
     *
     * @return bool
     */
    public function setIsSocial($isSocial);

    /**
     * Function getCreatedTime
     *
     * @return string
     */
    public function getCreatedTime();

    /**
     * Function setCreatedTime
     *
     * @param string $createdAt created at
     */
    public function setCreatedTime($createdAt);

    /**
     * Function getCustomerId
     *
     * @return integer
     */
    public function getCustomerId();

    /**
     * Function setCustomerId
     *
     * @param integer $customerId customer id
     */
    public function setCustomerId($customerId);
}
