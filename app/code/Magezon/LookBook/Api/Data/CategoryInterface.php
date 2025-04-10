<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\LookBook\Api\Data;

interface CategoryInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const CATEGORY_ID      = 'category_id';
    const IDENTIFIER       = 'identifier';
    const TITLE            = 'title';
    const IS_ACTIVE        = 'is_active';
    const POSITION         = 'position';
    const INCLUDE_IN_MENU  = 'include_in_menu';
    const CANONICAL_URL    = 'canonical_url';
    const DESCRIPTION      = 'description';
    const META_TITLE       = 'meta_title';
    const META_KEYWORDS    = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const PAGE_LAYOUT      = 'page_layout';
    const CREATION_TIME    = 'creation_time';
    const UPDATE_TIME      = 'update_time';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return CategoryInterface
     */
    public function setId($id);

    /**
     * Get Identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Set Identifier
     *
     * @param string $identifier
     * @return CategoryInterface
     */
    public function setIdentifier($identifier);

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set Title
     *
     * @param string $title
     * @return CategoryInterface
     */
    public function setTitle($title);

    /**
     * Is Active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Set Is Active
     *
     * @param int|bool $isActive
     * @return CategoryInterface
     */
    public function setIsActive($isActive);

    /**
     * Get Position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Set Position
     *
     * @param int $position
     * @return CategoryInterface
     */
    public function setPosition($position);

    /**
     * Get Include In Menu
     *
     * @return int|null
     */
    public function getIncludeInMenu();

    /**
     * Set Include In Menu
     *
     * @param int $includeInMenu
     * @return CategoryInterface
     */
    public function setIncludeInMenu($includeInMenu);

    /**
     * Get Canonical Url
     *
     * @return string|null
     */
    public function getCanonicalUrl();

    /**
     * Set  Canonical Url
     *
     * @param string $canonicalUrl
     * @return CategoryInterface
     */
    public function setCanonicalUrl($canonicalUrl);

    /**
     * Get Description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set Description
     *
     * @param string $description
     * @return CategoryInterface
     */
    public function setDescription($description);

    /**
     * Get Meta Title
     *
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * Set Meta Title
     *
     * @param string $metaTitle
     * @return CategoryInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * Get Meta Keywords
     *
     * @return string|null
     */
    public function getMetaKeywords();

    /**
     * Set Meta Keywords
     *
     * @param string $metaKeywords
     * @return CategoryInterface
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * Get Meta Description
     *
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * Set Meta Description
     *
     * @param string $metaDescription
     * @return CategoryInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * Get Page Layout
     *
     * @return string
     */
    public function getPageLayout();

    /**
     * Set Page Layout
     *
     * @param string $pageLayout
     * @return CategoryInterface
     */
    public function setPageLayout($pageLayout);

    /**
     * Get Creation Time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Set Creation Time
     *
     * @param string $creationTime
     * @return CategoryInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Get Update Time
     *
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Set Update Time
     *
     * @param string $updateTime
     * @return CategoryInterface
     */
    public function setUpdateTime($updateTime);
}