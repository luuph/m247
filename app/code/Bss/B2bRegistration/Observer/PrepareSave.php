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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2025 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Observer;

use Bss\B2bRegistration\Helper\Data;
use Bss\B2bRegistration\Model\Config\Source\CustomerAttribute;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class PrepareSave implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helper,
        LoggerInterface   $logger
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();
        if ($customer->getId() && $this->helper->isEnable()) {
            try {
                $approve = $this->helper->getApproveValue();
                $statusB2b = (int) $customer->getCustomAttribute('b2b_activasion_status')->getValue();
                if ($statusB2b == CustomerAttribute::B2B_APPROVAL && $approve) {
                    $customer->setCustomAttribute('activasion_status', $approve);
                }
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
