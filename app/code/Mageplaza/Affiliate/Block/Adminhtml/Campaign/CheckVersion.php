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

namespace Mageplaza\Affiliate\Block\Adminhtml\Campaign;

use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Mageplaza\Affiliate\Helper\Data;

/**
 * Class CheckVersion
 * @package Mageplaza\Affiliate\Block\Adminhtml\Campaign
 */
class CheckVersion extends Template
{
    /**
     * @var Data
     */
    protected  $helperData;

    /**
     * @param Template\Context $context
     * @param Data $helperData
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Template\Context $context,
        Data $helperData,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * @return bool
     */
    public function checkVersionAffiliate()
    {
        return !$this->helperData->isModuleOutputEnabled('Mageplaza_AffiliatePro');
    }
}
