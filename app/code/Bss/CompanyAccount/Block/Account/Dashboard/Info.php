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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Block\Account\Dashboard;

use Magento\Customer\Block\Form\Register;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\SubscriberFactory;

class Info extends \Magento\Customer\Block\Account\Dashboard\Info
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param CurrentCustomer $currentCustomer
     * @param SubscriberFactory $subscriberFactory
     * @param View $helperView
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrentCustomer $currentCustomer,
        SubscriberFactory $subscriberFactory,
        View $helperView,
        Session $customerSession,
        array $data = []
    ) {
        $this->customerSession =$customerSession;
        parent::__construct(
            $context,
            $currentCustomer,
            $subscriberFactory,
            $helperView,
            $data
        );
    }

    /**
     * @return mixed
     */
    public function getSubUser() {
        if ($subUser = $this->customerSession->getSubUser()) {
            return $subUser;
        }
        return null;
    }

    /**
     * Newsletter module availability
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isNewsletterEnabled()
    {
        if ($this->getSubUser()) {
            return false;
        }
        return $this->getLayout()
            ->getBlockSingleton(Register::class)
            ->isNewsletterEnabled();
    }
}
