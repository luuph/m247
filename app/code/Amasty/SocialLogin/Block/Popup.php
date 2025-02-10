<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Block;

use Amasty\Base\Model\MagentoVersion;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context;
use Magento\Framework\View\Element\Template;

class Popup extends Template
{
    public const VERSION_WITH_SHOW_PASSWORD = '2.4.3';
    public const LOGIN_FORM_POPUP_NAME = 'customer_form_login_popup';

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var \Amasty\SocialLogin\Model\ConfigData
     */
    private $configData;

    /**
     * @var \Amasty\SocialLogin\Model\Source\ButtonPosition
     */
    private $buttonPosition;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var Context
     */
    private $httpContext;

    public function __construct(
        MagentoVersion $magentoVersion,
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\SocialLogin\Model\ConfigData $configData,
        Context $httpContext,
        \Amasty\SocialLogin\Model\Source\ButtonPosition $buttonPosition,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->magentoVersion = $magentoVersion;
        $this->configData = $configData;
        $this->buttonPosition = $buttonPosition;
        $this->jsonEncoder = $jsonEncoder;
        $this->httpContext = $httpContext;
    }

    /**
     * @return bool
     */
    public function isSocialsEnabled()
    {
        return $this->configData->getConfigValue('general/enabled')
            && !$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * @return bool
     */
    public function isPopupEnabled()
    {
        return $this->configData->isPopupEnabled();
    }

    /**
     * @return string
     */
    public function getPositionTitle()
    {
        return $this->configData->getPositionTitle();
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        $customUrl = ltrim($this->configData->getConfigValue('general/custom_url'), '\/');
        return $this->jsonEncoder->encode([
            'logout_url'    => $this->getUrl('amsociallogin/logout/index'),
            'reset_pass_url' => $this->getUrl('customer/account/forgotpasswordpost'),
            'redirect_data' => [
                'url' => $this->_storeManager->getStore()->getBaseUrl() . $customUrl,
                'redirect' => $this->configData->getRedirectType()
            ],
            'close_when_clicked_outside' => $this->configData->isCloseWhenClickedOutside()
        ]);
    }

    public function isShowPasswordAvailable(): ?bool
    {
        return version_compare($this->magentoVersion->get(), self::VERSION_WITH_SHOW_PASSWORD, '>=');
    }

    /**
     * @param string $name
     * @return string
     */
    public function getChildHtmlAndReplaceIds(string $name): string
    {
        $formHtml = $this->getChildBlock($name)->toHtml();
        $formHtml = str_replace('email_address', 'am-email-address', $formHtml);
        $formHtml = str_replace('showPassword', $name .'_showPassword', $formHtml);
        $formHtml = str_replace('send2', str_replace('_', '-', $name) .'-send2', $formHtml);

        // WCAG fixes
        $formHtml = str_replace('"block-customer-login-heading"', '"am-block-customer-login-heading"', $formHtml);
        // NOTE: since m2.4.7 input has "password" id instead of "pass"
        $formHtml = str_replace('"pass"', '"am-pass"', $formHtml);

        if ($this->isLoginFormPopup($name)) {
            $formHtml = str_replace('id="password"', 'id="am-pass"', $formHtml);
        }

        return $formHtml;
    }

    private function isLoginFormPopup(string $name): bool
    {
        return $name === self::LOGIN_FORM_POPUP_NAME;
    }
}
