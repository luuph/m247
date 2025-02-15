<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Plugin\Multishipping\Helper;

/**
 * Class Data
 *
 * @package Bss\CompanyAccount\Plugin\Multishipping\Helper
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Data
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * Data constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Disable multishipping checkout if sub-user max order amount not allowed
     *
     * @param \Magento\Multishipping\Helper\Data $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsMultishippingCheckoutAvailable(
        \Magento\Multishipping\Helper\Data $subject,
        $result
    ) {
        if ($this->checkoutSession->getDisableCheckout()) {
            return false;
        }
        return $result;
    }
}
