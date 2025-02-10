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
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Controller\SubUser\ResetPasswordPost;
use Bss\CompanyAccount\Helper\FormHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class UpdatePassWord implements ObserverInterface
{
    /**
     * @var \Bss\CompanyAccount\Controller\SubUser\ResetPasswordPost
     */
    protected $resetPasswordPost;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var SubUserManagementInterface
     */
    private $subUserManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FormHelper
     */
    private $formHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubUserRepositoryInterface
     */
    protected $subUserRepository;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @param ResetPasswordPost $resetPasswordPost
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param FormHelper $formHelper
     * @param SubUserManagementInterface $subUserManagement
     * @param LoggerInterface $logger
     * @param SubUserRepositoryInterface $subUserRepository
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Bss\CompanyAccount\Controller\SubUser\ResetPasswordPost $resetPasswordPost,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        FormHelper $formHelper,
        SubUserManagementInterface $subUserManagement,
        LoggerInterface $logger,
        SubUserRepositoryInterface $subUserRepository,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        MessageManagerInterface $messageManager
    ) {
        $this->resetPasswordPost =$resetPasswordPost;
        $this->session = $customerSession;
        $this->subUserManagement = $subUserManagement;
        $this->storeManager = $storeManager;
        $this->formHelper = $formHelper;
        $this->logger = $logger;
        $this->subUserRepository = $subUserRepository;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getData('request');
        if (!$this->formHelper->validate($request)) {
            return $this->resultRedirectFactory->create()
                ->setPath('customer/account/edit');
        }
        $resetPasswordToken = (string)$request->getQuery('token');
        if ($resetPasswordToken) {
            /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
            $subUser = $this
                ->subUserManagement
                ->getSubUserBy($resetPasswordToken, 'token', $this->storeManager->getWebsite()->getId());
        } else {
            $subUser = $this->subUserRepository->getById($this->session->getSubUser()->getSubId());
        }
        return $this->validateAndResetPassword($resetPasswordToken, $subUser, $request);
    }

    /**
     * Validate input and reset password
     *
     * @param string|null $resetPasswordToken
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     * @return \Magento\Framework\Controller\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function validateAndResetPassword(
        $resetPasswordToken,
        $subUser,
        $request
    ) {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $websiteId = $this->storeManager->getWebsite()->getId();
            $password = (string)$request->getPost('password');
            $passwordConfirmation = (string)$request->getPost('password_confirmation');
            $canUpdate = true;
            $redirectPath = "customer/account/edit";
            if (!$resetPasswordToken) {
                $currentPass = $request->getPost('current_password');
                $isCorrectCurrentPassword = $this->subUserManagement->authenticate($subUser, $currentPass);
                if (!$isCorrectCurrentPassword) {
                    $this->messageManager->addErrorMessage(__('Current Password didn\'t match. Please try again.'));
                    $request->setParams(['save_pass_error' => true]);
                    $canUpdate = false;
                }
            } else {
                if (!$subUser) {
                    $resultRedirect->setPath('customer/account/forgotpassword/');
                    throw new ExpiredException(__('The password token is incorrect. Reset and try again.'));
                }
                $this->subUserManagement->validateResetPasswordLinkToken(null, $resetPasswordToken, $websiteId);
                $resultRedirect->setPath('*/*/createpassword', ['token' => $resetPasswordToken]);
            }
            if (empty($password)) {
                $this->messageManager->addErrorMessage(__('Please enter a new password.'));
                $canUpdate = false;
            }
            if ($password !== $passwordConfirmation) {
                $this->messageManager->addErrorMessage(
                    __('New Password and Confirm New Password values didn\'t match.')
                );
                $canUpdate = false;
            }

            if ($canUpdate) {
                $this->subUserManagement->resetPassword(
                    !$resetPasswordToken ? $subUser->getSubId() : null,
                    $resetPasswordToken,
                    $password,
                    $websiteId
                );
            }

        } catch (InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addErrorMessage($error->getMessage());
            }
        } catch (ExpiredException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the new password.'));
        }
        return $resultRedirect->setPath($redirectPath);
    }
}
