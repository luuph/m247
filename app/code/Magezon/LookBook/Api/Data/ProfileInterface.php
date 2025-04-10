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

interface ProfileInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const PROFILE_ID                = 'profile_id';
    const IS_ACTIVE                 = 'is_active';
    const TITLE                     = 'title';
    const IDENTIFIER                = 'identifier';
    const DESCRIPTION               = 'description';
    const IMAGE                     = 'image';
    const MARKER                    = 'marker';
    const LAYOUT_TYPE               = 'layout_type';
    const CREATION_TIME             = 'creation_time';
    const UPDATE_TIME               = 'update_time';
    const PAGE_LAYOUT               = 'page_layout';
    const CUSTOM_LAYOUT_UPDATE_XML  = 'custom_layout_update_xml';
    const META_TITLE                = 'meta_title';
    const META_KEYWORDS             = 'meta_keywords';
    const META_DESCRIPTION          = 'meta_description';
    

    /**
     * Get Store ID
     * 
     * @return int|null
     */
    public function getId();

    /**
     * Set Store ID
     *
     * @param int $id
     * @return ProfileInterface
     */
    public function setId($id);
    
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string
     * @return ProfileInterface
    */
    public function setTitle($title);


    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @param string
     * @return ProfileInterface
    */
    public function setIdentifier($identifier);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string
     * @return ProfileInterface
    */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getImage();

    /**
     * @param string
     * @return ProfileInterface
    */
    public function setImage($image);

    /**
     * @param string
     * @return ProfileInterface
    */
    public function setMarker($marker);

    /**
     * @return string
     */
    public function getLayoutType();

    /**
     * @param string
     * @return ProfileInterface
    */
    public function setLayoutType($layout_type);

    /**
     * @return string
     */
    public function getCreationTime();

    /**
     * @param string
     * @return ProfileInterface
    */
    public function setCreationTime($creation_time);

    /**
     * Get Page Layout
     *
     * @return string
     */
    public function getPageLayout();

    /**
     * Set Page Layout
     *
     * @param $pageLayout
     * @return ProfileInterface
     */
    public function setPageLayout($pageLayout);

    /**
     * Get Custom Layout Update Xml
     *
     * @return string|null
     */
    public function getCustomLayoutUpdateXml();

    /**
     * Set Custom Layout Update Xml
     *
     * @param string $customLayoutUpdateXml
     * @return ProfileInterface
     */
    public function setCustomLayoutUpdateXml($customLayoutUpdateXml);

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
     * @return ProfileInterface
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
     * @return ProfileInterface
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
     * @return ProfileInterface
     */
    public function setMetaDescription($metaDescription);
}