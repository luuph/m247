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
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliatePro\Model\Api;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Mageplaza\AffiliatePro\Api\Data\BannerInterface;
use Mageplaza\AffiliatePro\Api\Data\BannerExtensionInterface;

/**
 * Class Banner
 * @package Mageplaza\AffiliatePro\Model\Api
 */
class Banner extends AbstractExtensibleModel implements
    BannerInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('Mageplaza\AffiliatePro\Model\ResourceModel\Banner');
    }

    public function getBannerId()
    {
        return $this->getData(self::BANNER_ID);
    }

    public function setBannerId($value)
    {
        return $this->setData(self::BANNER_ID, $value);
    }

    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    public function setTitle($value)
    {
        return $this->setData(self::TITLE, $value);
    }

    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    public function setContent($value)
    {
        return $this->setData(self::CONTENT, $value);
    }

    public function getLink()
    {
        return $this->getData(self::LINK);
    }

    public function setLink($value)
    {
        return $this->setData(self::LINK, $value);
    }

    public function getRelNofollow()
    {
        return $this->getData(self::REL_NOFOLLOW);
    }

    public function setRelNofollow($value)
    {
        return $this->setData(self::REL_NOFOLLOW, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCampaignId()
    {
        return $this->getData(self::CAMPAIGN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCampaignId($value)
    {
        return $this->setData(self::CAMPAIGN_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($value)
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($value)
    {
        return $this->setData(self::UPDATED_AT, $value);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return ExtensionAttributesInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param BannerExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        BannerExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
