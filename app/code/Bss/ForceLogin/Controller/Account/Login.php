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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ForceLogin\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\Registry;

class Login extends \Magento\Customer\Controller\Account\Login
{
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    protected $resultPageFactory;
    /**
     * @var CatalogSession
     */
    protected $catalogSession;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Login constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param CatalogSession $catalogSession
     * @param Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        CatalogSession $catalogSession,
        Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $resultPageFactory
        );
        $this->catalogSession = $catalogSession;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $previousUrl = $this->_redirect->getRefererUrl();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $controllerName = str_replace($baseUrl, "", $previousUrl);
        if ($controllerName == "customer/account/createpassword/") {
            $previousUrl = $baseUrl.'customer/account/index';
            $this->catalogSession->setBssPreviousUrl($previousUrl);
        } else {
            $this->catalogSession->setBssPreviousUrl($previousUrl);
        }
        return parent::execute();
    }
}
