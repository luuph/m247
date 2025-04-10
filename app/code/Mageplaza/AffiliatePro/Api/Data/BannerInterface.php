<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AffiliatePro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliatePro\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface BannerInterface
 * @api
 */
interface BannerInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BANNER_ID = 'banner_id';

    const TITLE = 'title';

    const CONTENT = 'content';

    const LINK = 'link';

    const STATUS = 'status';

    const REL_NOFOLLOW = 'rel_nofollow';

    const CAMPAIGN_ID = 'campaign_id';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    /**#@-*/

    /**
     * @return int
     */
    public function getBannerId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setBannerId($value);

    /**
     * @return int
     */
    public function getCampaignId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setCampaignId($value);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setTitle($value);

    /**
     * @return string
     */
    public function getContent();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setContent($value);

    /**
     * @return string
     */
    public function getLink();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setLink($value);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setStatus($value);

    /**
     * @return int
     */
    public function getRelNofollow();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setRelNofollow($value);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCreatedAt($value);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setUpdatedAt($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Mageplaza\AffiliatePro\Api\Data\BannerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Mageplaza\AffiliatePro\Api\Data\BannerExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        BannerExtensionInterface $extensionAttributes
    );
}
