<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Model\AdvancedReview;

use Amasty\AdvancedReview\Model\Frontend\Review\IsAllowWriteReview;
use Amasty\Base\Model\Di\Wrapper as IsAllowWriteReviewDi;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Module\Manager;

class UseDefaultButtonUrl
{
    /**
     * @var bool|null
     */
    private $result = null;

    /**
     * @var IsAllowWriteReview
     */
    private $isAllowWriteReview;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        Manager $moduleManager,
        CustomerSession $customerSession,
        IsAllowWriteReviewDi $isAllowWriteReview = null
    ) {
        $this->moduleManager = $moduleManager;
        $this->customerSession = $customerSession;
        $this->isAllowWriteReview = $isAllowWriteReview;
    }

    public function execute(): bool
    {
        if ($this->result === null
            && $this->moduleManager->isEnabled('Amasty_AdvancedReview')
        ) {
            $this->result = $this->isAllowWriteReview->execute() || $this->customerSession->isLoggedIn();
        }

        return $this->result ?? true;
    }
}
