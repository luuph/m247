<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Block\Adminhtml\System\Config;

use Amasty\SocialLogin\Model\SocialData;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class OldRedirectUrlByProvider extends Field
{
    /**
     * @var SocialData
     */
    private $socialData;

    /**
     * @var string
     */
    private $socialName;

    public function __construct(
        Context $context,
        SocialData $socialData,
        string $socialName = '',
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->socialData = $socialData;
        $this->socialName = $socialName;
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $authUrl = $this->socialData->getBaseAuthUrl();
        $redirectUrl = $authUrl . (strpos($authUrl, '?') ? '&' : '?')
            . 'hauth.done=' . $this->socialName;

        return $this->getFieldTemplate($element, $redirectUrl);
    }

    private function getFieldTemplate(AbstractElement $element, string $redirectUrl): string
    {
        $html = '<input style="opacity:1;" readonly id="%s" class="input-text admin__control-text"
                        value="%s" onclick="this.select()" type="text">';

        return sprintf($html, $element->getHtmlId(), $redirectUrl);
    }
}
