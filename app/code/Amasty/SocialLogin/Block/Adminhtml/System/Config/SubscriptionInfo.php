<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Helper\Js;

class SubscriptionInfo extends Fieldset
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var string
     */
    private $message;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        Manager $moduleManager,
        string $moduleName,
        string $message,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->moduleManager = $moduleManager;
        $this->moduleName = $moduleName;
        $this->message = $message;
    }

    public function render(AbstractElement $element): string
    {
        if ($this->moduleName === null || $this->moduleManager->isEnabled($this->moduleName)) {
            return '';
        }

        return $this->renderNotification();
    }

    public function renderNotification(): string
    {
        return <<<NOTIFICATION
            <div>
                <p class="message message-notice">$this->message</p>
            </div>
        NOTIFICATION;
    }
}
