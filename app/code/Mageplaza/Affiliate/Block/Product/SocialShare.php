<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Block\Product;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Affiliate\Helper\Data as AffiliateHelper;

/**
 * Class SocialShare
 * @package Mageplaza\Affiliate\Block\Product
 */
class SocialShare extends Template
{
    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var AffiliateHelper
     */
    protected $_affiliateHelper;

    /**
     * Account constructor.
     *
     * @param Context $context
     * @param HttpContext $httpContext
     * @param AffiliateHelper $affiliateHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        HttpContext $httpContext,
        AffiliateHelper $affiliateHelper,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->_affiliateHelper   = $affiliateHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_affiliateHelper->isEnabled() ||
            !$this->_affiliateHelper->isEnableReferFriend() ||
            !(bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)
        ) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @return AffiliateHelper
     */
    public function getAffiliateHelper()
    {
        return $this->_affiliateHelper;
    }

    /**
     * @return string
     */
    public function getShareThisWidgettUrl()
    {
        return "//platform-api.sharethis.com/js/sharethis.js#property=" . $this->_affiliateHelper->getShareThisPropertyId();
    }
}
