<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Block\Form\Registration;

use Amasty\CustomerAttributes\Block\Customer\Form\Attributes;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class CustomerAttributes extends Template
{
    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        Manager $moduleManager,
        Context $context,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $data);
    }

    protected function _toHtml()
    {
        if ($this->moduleManager->isEnabled('Amasty_CustomerAttributes')) {
            $attributes = $this
                ->getLayout()
                ->createBlock(Attributes::class) //@phpstan-ignore class.notFound
                ->setTemplate('Amasty_CustomerAttributes::attributes.phtml');

            return $attributes->toHtml();
        }

        return '';
    }
}
