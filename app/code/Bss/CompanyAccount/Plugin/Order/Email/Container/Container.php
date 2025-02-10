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

namespace Bss\CompanyAccount\Plugin\Order\Email\Container;

use Magento\Customer\Model\Session;

/**
 * Class Container
 *
 * @package Bss\CompanyAccount\Plugin\Order\Email\Container
 */
class Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Container constructor.
     *
     * @param \Magento\Framework\Registry $registry
     * @param Session $customerSession
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        Session                     $customerSession
    ) {
        $this->registry = $registry;
        $this->customerSession = $customerSession;
    }

    /**
     * If order was placed by sub-user will return sub-user email
     *
     * @param \Magento\Sales\Model\Order\Email\Container\Container $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCustomerEmail(\Magento\Sales\Model\Order\Email\Container\Container $subject, $result)
    {
        /** 
         * Session is used for the case where a sub-user places an order, and the email is sent before the event 
         * checkout_submit_all_after ở \Magento\Checkout\Model\Type\Onepage::saveOrder
         */
        $subUserEmail = '';
        $subUserEmail = $this->getSubUserEmail();
        if($subUser = $this->customerSession->getSubUser()) {
            $subUserEmail = $subUser->getSubEmail();
        }
        if ($subUserEmail) {
            return $subUserEmail;
        }
        return $result;
    }

    /**
     * Get registered sub-user
     *
     * @return \Bss\CompanyAccount\Api\Data\SubUserInterface
     */
    protected function getSubUserEmail()
    {
        return $this->registry->registry('bss_is_send_mail_to_sub_user');
    }
}
