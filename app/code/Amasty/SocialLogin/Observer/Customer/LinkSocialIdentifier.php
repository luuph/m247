<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Observer\Customer;

use Amasty\SocialLogin\Model\Repository\SocialRepository;
use Amasty\SocialLogin\Model\SocialData;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class LinkSocialIdentifier implements ObserverInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var SocialRepository
     */
    private $socialRepository;

    /**
     * @var SocialData
     */
    private $socialData;

    public function __construct(
        CustomerSession $customerSession,
        SocialRepository $socialRepository,
        SocialData $socialData
    ) {
        $this->customerSession = $customerSession;
        $this->socialRepository = $socialRepository;
        $this->socialData = $socialData;
    }

    public function execute(Observer $observer)
    {
        /** @var CustomerInterface $customer */
        $customer = $observer->getData('customer');
        $userData = $this->customerSession->getUserProfile();

        if (!$userData || !$customer) {
            return;
        }

        $type = $this->customerSession->getType();
        $user = $this->socialData->createUserData($userData, $type);
        $this->socialRepository->createCustomer($user);
        $this->socialRepository->createSocialAccount($user, $customer->getId(), $type);
        $this->customerSession->setUserProfile(null);

        if ($this->customerSession->getCustomer()) {
            $this->customerSession->setAmSocialIdentifier($userData->identifier);
        }
    }
}
