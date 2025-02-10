<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Model;

use Amasty\SocialLogin\Api\Data\SocialInterface;
use Magento\Framework\App\ObjectManager;

class Social extends \Magento\Framework\Model\AbstractModel implements SocialInterface
{
    /**
     * @deprecared
     */
    public const TYPE_APPLE = \Amasty\SocialLogin\Model\SocialList::TYPE_APPLE;

    protected function _construct()
    {
        $this->_init(\Amasty\SocialLogin\Model\ResourceModel\Social::class);
    }

    /**
     * @deprecared moved to SocialData::getUserProfile
     */
    public function getUserProfile(string $type)
    {
        return ObjectManager::getInstance()->get(SocialData::class)->getUserProfile($type);
    }

    /**
     * @deprecared moved to SocialData::getProviderData
     */
    public function getProviderData($type): array
    {
        return ObjectManager::getInstance()->get(SocialData::class)->getProviderData($type);
    }

    /**
     * @inheritdoc
     */
    public function getSocialId()
    {
        return $this->_getData(SocialInterface::SOCIAL_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSocialId($socialId)
    {
        $this->setData(SocialInterface::SOCIAL_ID, $socialId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(SocialInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData(SocialInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->_getData(SocialInterface::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->setData(SocialInterface::TYPE, $type);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(SocialInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(SocialInterface::NAME, $name);

        return $this;
    }
}
