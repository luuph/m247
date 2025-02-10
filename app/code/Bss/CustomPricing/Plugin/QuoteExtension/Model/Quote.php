<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2024-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricing\Plugin\QuoteExtension\Model;

use Magento\Framework\Exception\LocalizedException;

class Quote
{
    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helperData;

    /**
     * @param \Bss\CustomPricing\Helper\Data $helperData
     */
    public function __construct(
        \Bss\CustomPricing\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param \Bss\QuoteExtension\Model\Quote $subject
     * @param $quote
     * @return void
     * @throws LocalizedException
     */
    public function beforeCloneQuoteExtension($subject, &$quote)
    {
        if ($this->helperData->isEnabled()) {
            $customerId = $subject->getData('customer_id');
            $customerGroup = $subject->getData('customer_group_id');
            $backendSession = $this->helperData->getBackendSession();
            if ($backendSession) {
                $backendSession->setQECustomerID($customerId);
                $backendSession->setQECustomerGroup($customerGroup);
            }
        }
    }
}
