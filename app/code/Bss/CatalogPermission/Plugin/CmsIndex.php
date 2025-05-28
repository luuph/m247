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
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2025 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CatalogPermission\Plugin;

use Bss\CatalogPermission\Helper\CheckPermission;
use Bss\CatalogPermission\Helper\ModuleConfig;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

class CmsIndex
{
    /**
     * @var \Bss\CatalogPermission\Helper\ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Bss\CatalogPermission\Helper\Data
     */
    protected $helper;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CheckPermission
     */
    protected $helperPermission;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @param ModuleConfig $moduleConfig
     * @param \Bss\CatalogPermission\Helper\Data $helper
     * @param Session $customerSession
     * @param RedirectInterface $redirect
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Bss\CatalogPermission\Helper\CheckPermission $helperPermission
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     */
    public function __construct(
        ModuleConfig $moduleConfig,
        \Bss\CatalogPermission\Helper\Data $helper,
        Session $customerSession,
        RedirectInterface $redirect,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Bss\CatalogPermission\Helper\CheckPermission $helperPermission,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->helperPermission = $helperPermission;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @param \Magento\Cms\Controller\Index\Index $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute(
        \Magento\Cms\Controller\Index\Index $subject,
        \Closure $proceed
    ) {
        $result = $proceed();
        $isEnableHomepage = $this->moduleConfig->enableHomePgae();
        if (!$isEnableHomepage) {
            return $result;
        }
        $homepageMessage = $this->moduleConfig->getHomePageErrorMessage();
        $redirectPage = $this->moduleConfig->getHomePageRedirect();
        $restrictedCustomerGroups = $this->moduleConfig->getRestrictedCustomerGroups();
        $arrayRestricted = [];
        if ($restrictedCustomerGroups === "0" || $restrictedCustomerGroups) {
            $arrayRestricted = explode(',', $restrictedCustomerGroups);
        }
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        if ($arrayRestricted && in_array($customerGroupId, $arrayRestricted)) {
            $customUrl = $this->moduleConfig->getHomePageUrlLink();
            if (!$customUrl || $this->helperPermission->checkCustomUrl($customUrl)) {
                $this->returnMessage($homepageMessage);
                return $this->redirectFactory->create()->setPath('no-route');
            }
            $referentUrl = $this->redirect->getRefererUrl();
            $currentUrl = $subject->getRequest()->getUriString();
            $redirectPath = $this->helper->getRedirectUrl($redirectPage, $customUrl, $referentUrl);
            if ($redirectPath == $currentUrl) {
                $this->returnMessage($homepageMessage);
                return $this->redirectFactory->create()->setPath('no-route');
            }
            if ($redirectPath) {
                $this->returnMessage($homepageMessage);
                return $this->redirectFactory->create()->setPath($redirectPath);
            }
        }
        return $result;
    }

    /**
     * Return error message
     *
     * @param string $message
     */
    private function returnMessage($message)
    {
        if ($message) {
            $this->messageManager->addErrorMessage($message);
        }
    }
}
