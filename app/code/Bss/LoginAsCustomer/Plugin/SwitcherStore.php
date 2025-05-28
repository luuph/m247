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
 * @package    Bss_LoginAsCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\LoginAsCustomer\Plugin;

use Bss\LoginAsCustomer\Helper\SwitchStore;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\Account\LoginPost;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class SwitcherStore
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var SwitchStore
     */
    private $switchStoreview;

    /**
     * SwitcherStore constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param UrlInterface $url
     * @param SwitchStore $switchStoreview
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepositoryInterface,
        UrlInterface $url,
        SwitchStore $switchStoreview
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->url = $url;
        $this->switchStoreview = $switchStoreview;
    }

    /**
     * After Customer Login
     *
     * @param LoginPost $subject
     * @param string $result
     * @return mixed
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Store\Model\StoreSwitcher\CannotSwitchStoreException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        LoginPost $subject,
        $result
    ) {
        $customerId = $this->customerSession->getCustomerId();
        $storeId = null;
        if ($customerId) {
            if ($this->customerRepositoryInterface->getById($customerId)) {
                $customerStoreId = $this->customerRepositoryInterface->getById($customerId)->getStoreId();
            } else {
                $customerStoreId = null;
            }

            if ($customerStoreId !== null) {
                $storeId = $customerStoreId;
            }
            $storeCode = $this->storeManager->getStore($storeId)->getCode();
            $url = $this->url->getCurrentUrl();
            $this->switchStoreview->switchStoreView($url, $storeCode);
        }
        return $result;
    }
}
