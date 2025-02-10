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
 * @package    Bss_ForceLogin
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ForceLogin\Plugin\Cms\Index;

use Bss\ForceLogin\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Session as CatalogSession;

class Index
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CatalogSession
     */
    protected $catalogSession;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param Data $helperData
     * @param Session $customerSession
     * @param CatalogSession $catalogSession
     */
    public function __construct(
        Context $context,
        Data $helperData,
        Session $customerSession,
        CatalogSession $catalogSession
    ) {
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        $this->url = $context->getUrl();
        $this->messageManager = $context->getMessageManager();
        $this->catalogSession = $catalogSession;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    /**
     * Force Login for Cms Page
     * @param \Magento\Cms\Controller\Index\Index $subject
     * @param \Closure $proceed
     * @param null $coreRoute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(\Magento\Cms\Controller\Index\Index $subject, \Closure $proceed, $coreRoute = null)
    {
        $pageId = $this->helperData->getCmsPageConfig(\Magento\Cms\Helper\Page::XML_PATH_HOME_PAGE);
        $enableLogin = $this->helperData->isEnable();
        $enableCmsPage = $this->helperData->isEnableCmsPage();
        $forceCmsPageId = $this->helperData->getCmsPageId();
        if ($enableLogin && $enableCmsPage) {
            $customerLogin = $this->customerSession->isLoggedIn();
            if (!$customerLogin && $forceCmsPageId === $pageId) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $currentUrl = $this->url->getCurrentUrl();
                $this->catalogSession->setBssCurrentUrl($currentUrl);
                $message = $this->helperData->getAlertMessage();
                if ($message) {
                    $this->messageManager->addErrorMessage($message);
                }
                return $resultRedirect->setPath('customer/account/login');
            } else {
                return $proceed();
            }
        } else {
            return $proceed();
        }
    }
}
