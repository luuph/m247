<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Plugin\AdvancedReview\ViewModel\Reviews\Product\View\ListView\WriteReviewButton;

use Amasty\AdvancedReview\ViewModel\Reviews\Product\View\ListView\WriteReviewButton as WriteReviewButton;
use Amasty\SocialLogin\Model\AdvancedReview\UseDefaultButtonUrl;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\Manager;
use Magento\Framework\UrlInterface;

class AllowWriteReviewButton
{
    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var UseDefaultButtonUrl
     */
    private $useDefaultButtonUrl;

    public function __construct(
        SessionFactory $sessionFactory,
        UrlInterface $urlBuilder,
        Manager $moduleManager,
        UseDefaultButtonUrl $useDefaultButtonUrl = null
    ) {
        $this->sessionFactory = $sessionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->moduleManager = $moduleManager;
        $this->useDefaultButtonUrl = $useDefaultButtonUrl
            ?? ObjectManager::getInstance()->get(UseDefaultButtonUrl::class);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param WriteReviewButton $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsCanRender($subject, bool $result): bool
    {
        return true;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param WriteReviewButton $subject
     * @param string $result
     * @return string
     */
    public function afterGetButtonUrl($subject, string $result): string
    {
        if (!$this->moduleManager->isEnabled('Amasty_JetTheme')
            && !$this->useDefaultButtonUrl->execute()
        ) {
            return $this->urlBuilder->getUrl('customer/account/login');
        }

        return $result;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        return $this->sessionFactory->create();
    }
}
