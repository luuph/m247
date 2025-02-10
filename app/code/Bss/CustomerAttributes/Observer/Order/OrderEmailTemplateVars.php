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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Observer\Order;

class OrderEmailTemplateVars implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Bss\CustomerAttributes\Model\Email\EmailTemplateVars
     */
    protected $helperEmail;

    /**
     * OrderEmailTemplateVars constructor
     *
     * @param \Bss\CustomerAttributes\Model\Email\EmailTemplateVars $helperEmail
     */
    public function __construct(
        \Bss\CustomerAttributes\Model\Email\EmailTemplateVars $helperEmail
    ) {
        $this->helperEmail = $helperEmail;
    }

    /**
     * Set email variable
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($observer->getData('transportObject') !== null) {
            $transport = $observer->getData('transportObject');
        } else {
            $transport = $observer->getData('transport');
        }
        $order = $transport['order'];
        $this->helperEmail->setEmailTemplateVars($transport, $order);
    }
}
