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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Controller\Add;

use Bss\ProductStockAlert\Controller\Add as AddController;
use Bss\ProductStockAlert\Model\PriceAlertFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;

class PriceAlert extends AddController implements HttpPostActionInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Bss\ProductStockAlert\Model\PriceAlertFactory
     */
    protected $modelPriceFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $store;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * Construct.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param PriceAlertFactory $modelPriceFactory
     * @param StoreManagerInterface $store
     * @param Customer $customer
     * @param \Magento\Catalog\Model\ProductFactory $product
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        \Bss\ProductStockAlert\Model\PriceAlertFactory $modelPriceFactory,
        \Magento\Store\Model\StoreManagerInterface $store,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Catalog\Model\ProductFactory $product
    ) {
        $this->product = $product;
        $this->modelPriceFactory = $modelPriceFactory;
        $this->store = $store;
        $this->customer = $customer;
        parent::__construct($context, $customerSession);
    }

    /**
     * Add price alert.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('product_id');
        $customerEmail = $this->getRequest()->getParam('pricealert_email');

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$productId || !$customerEmail) {
            $this->messageManager->addErrorMessage(__('Invalid value input. Please try again.'));
            $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
            return $resultRedirect;
        }
        $backProductUrl = (string)$this->getRequest()->getParam('url_product');

        try {
            if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                $this->messageManager->addErrorMessage(__('Please correct this email address: %1', $customerEmail));
                $resultRedirect->setUrl($backProductUrl);
                return $resultRedirect;
            }

            $parentIdParam = (int)$this->getRequest()->getParam('parent_id');
            $initialPrice = (float)$this->getRequest()->getParam('initial_price');
            $productSku = (string)$this->getRequest()->getParam('product_sku');
            $currencyCode = (string)$this->getRequest()->getParam('currency_code');

            $parentId = ($parentIdParam && $parentIdParam != $productId) ? $parentIdParam : null;
            $customerId = $this->customerSession->getCustomerId() ? $this->customerSession->getCustomerId() : 0;
            $customerData = $this->customer->load($customerId);
            $customerName = $customerId ? $customerData->getFirstname() . " " . $customerData->getLastname() : "Guest";
            $customerGroup = $customerId ? $customerData->getGroupId() : 0;

            if (!$initialPrice) {
                $product = $this->product->create()->load($productId);
                $initialPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
            }

            $websiteId = $this->store->getStore()->getWebsiteId();

            $model = $this->modelPriceFactory->create()
                ->setCustomerId($customerId)
                ->setCustomerEmail($customerEmail)
                ->setCustomerName($customerName)
                ->setCustomerGroup($customerGroup)
                ->setProductSku($productSku)
                ->setProductId($productId)
                ->setInitialPrice($initialPrice)
                ->setCurrencyCode($currencyCode)
                ->setWebsiteId($websiteId)
                ->setStoreId($this->store->getStore()->getId())
                ->setParentId($parentId);
            $model->save();

            //set Notify
            $this->setNotifyPriceSubscription($customerId, $customerEmail, $productId);

            $this->messageManager->addSuccessMessage(__('Price Alert Subscription has been saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the alert subscription right now. Maybe this email has been used for this product before!'));
        }

        $resultRedirect->setUrl($backProductUrl);

        return $resultRedirect;
    }

    /**
     * Set session price notify.
     *
     * @param int $customerId
     * @param string $customerEmail
     * @param int $productId
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setNotifyPriceSubscription($customerId, $customerEmail, $productId)
    {
        if ($customerId == 0) {
            $notify = $this->customerSession->getNotifyPriceSubscription();
            if (!empty($notify)) {
                $notify[$productId] = [
                    "email" => $customerEmail,
                    "website" => $this->store->getStore()->getWebsiteId()
                ];
            } else {
                $notify[$productId] = [
                    "email" => $customerEmail,
                    "website" => $this->store->getStore()->getWebsiteId()
                ];
            }
            $this->customerSession->setNotifyPriceSubscription($notify);
        }
        return $this;
    }
}
