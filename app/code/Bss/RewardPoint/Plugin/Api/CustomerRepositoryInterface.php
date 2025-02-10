<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Plugin\Api;

/**
 * Class Customer Repository Interface
 *
 * Bss\RewardPoint\Plugin\Api
 */
class CustomerRepositoryInterface
{
    public const NO = 0;
    /**
     * @var \Bss\RewardPoint\Model\RewardPointRepository
     */
    protected $repository;

    /**
     * CustomerRepositoryInterface constructor.
     * @param \Bss\RewardPoint\Model\RewardPointRepository $repository
     */
    public function __construct(
        \Bss\RewardPoint\Model\RewardPointRepository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * Set extension_attribute
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customer
     * @param \Magento\Customer\Api\Data\CustomerInterface $entity
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetById(
        \Magento\Customer\Api\CustomerRepositoryInterface $customer,
        \Magento\Customer\Api\Data\CustomerInterface $entity
    ) {
        $rewardPoint = $this->repository;
        $extensionAttributes = $entity->getExtensionAttributes();
        $notifyBalance = $rewardPoint->getNotifyBalance($entity);
        $notifyExpiration =  $rewardPoint->getNotifyExpiration($entity);
        if ($extensionAttributes) {
            $extensionAttributes->setRwpWebsiteId($rewardPoint->getWebsiteId());
            $extensionAttributes->setRwpPoint($rewardPoint->getPoint($entity));
            $extensionAttributes->setRwpPointUsed($rewardPoint->getPointUsed($entity));
            $extensionAttributes->setRwpPointExpired($rewardPoint->getPointExpired($entity));
            $extensionAttributes->setRwpAmount($rewardPoint->getAmount($entity));
            $extensionAttributes->setRwpNotifyBalance($notifyBalance ? $notifyBalance: self::NO);
            $extensionAttributes->setRwpNotifyExpiration($notifyExpiration ? $notifyExpiration: self::NO);
            $extensionAttributes->setRwpRatePoint($rewardPoint->getRatePoint($entity));
            $entity->setExtensionAttributes($extensionAttributes);
        }
        return $entity;
    }
}
