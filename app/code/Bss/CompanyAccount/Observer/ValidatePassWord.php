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
 * @copyright  Copyright (c) 2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Observer;

use Bss\CompanyAccount\Api\SubUserManagementInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

class ValidatePassWord implements ObserverInterface
{
    /**
     * @var SubUserManagementInterface
     */
    private $subUserManagement;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @param SubUserManagementInterface $subUserManagement
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        SubUserManagementInterface $subUserManagement,
        MessageManagerInterface $messageManager
    ) {
        $this->subUserManagement = $subUserManagement;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Bss\CompanyAccount\Exception\EmptyInputException
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getData('request');
        $subUser = $observer->getData('subUser');
        $currentPass = $request->getPost('current_password');
        $isCorrectCurrentPassword = $this->subUserManagement->authenticate($subUser, $currentPass);
        if (!$isCorrectCurrentPassword) {
            $this->messageManager->addErrorMessage(__('Current Password didn\'t match. Please try again.'));
            $request->setParams(['save_pass_error' => true]);
        }
    }
}
