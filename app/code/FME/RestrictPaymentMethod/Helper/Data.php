<?php
/**
 * FME Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the fmeextensions.com license that is
 * available through the world-wide-web at this URL:
 * https://www.fmeextensions.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @copyright Copyright (c) 2019 FME (http://fmeextensions.com/)
 * @license   https://fmeextensions.com/LICENSE.txt
 */

namespace FME\RestrictPaymentMethod\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;
    protected $storeManager;
    protected $productMetadata;


    const XML_RESTRICTPAYMENTMETHOD_ENABLE = 'paymentmethod/general/enabled';

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        UrlInterface $backendUrl,
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $productMetadata
    ) {
        $this->backendUrl = $backendUrl;
        $this->storeManager = $storeManager;
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }

    public function isEnabledInFrontend()
    {
        $isEnabled = true;
        $enabled = $this->scopeConfig->getValue(self::XML_RESTRICTPAYMENTMETHOD_ENABLE, ScopeInterface::SCOPE_STORE);
        if ($enabled == null || $enabled == '0') {
            $isEnabled = false;
        }
        return $isEnabled;
    }
}
