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
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ForceLogin\Plugin\Account;

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\PageFactory;

class Create
{
    /**
     * @var \Bss\ForceLogin\Helper\Data
     */
    protected $data;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Bss\ForceLogin\Helper\Data $data
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Bss\ForceLogin\Helper\Data $data,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->messageManager = $messageManager;
        $this->data = $data;
        $this->urlInterface = $urlInterface;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * @param Create $subject
     * @param Redirect $result
     * @return Redirect
     */
    public function afterExecute($subject, $result)
    {
        if ($this->data->isEnable() && $this->data->isDisableRegister()) {
            $currentUrl = $this->urlInterface->getCurrentUrl();
            if (strpos($currentUrl, "customer/account/create")) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('customer/account/login');
                return $resultRedirect;
            }
        }
        return $result;
    }

}
