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
namespace Bss\ProductStockAlert\Controller\Unsubscribe;

use Bss\ProductStockAlert\Controller\Unsubscribe as UnsubscribeController;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class PriceAlert extends UnsubscribeController
{
    /**
     * @var ProductFactory
     */
    protected $product;

    /**
     * @var \Bss\ProductStockAlert\Model\PriceAlert
     */
    protected $modelStock;

    /**
     * @var StoreManagerInterface
     */
    protected $store;

    /**
     * Stock constructor.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param \Bss\ProductStockAlert\Model\PriceAlert $modelStock
     * @param StoreManagerInterface $store
     * @param ProductFactory $product
     */
    public function __construct(
        Context                                        $context,
        CustomerSession                                $customerSession,
        \Bss\ProductStockAlert\Model\PriceAlert        $modelStock,
        StoreManagerInterface                          $store,
        ProductFactory                                 $product
    ) {
        $this->product = $product;
        $this->modelStock = $modelStock;
        $this->store = $store;
        parent::__construct($context, $customerSession);
    }

    /**
     * Unsubscribe price alert
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $productId = (int)$this->getRequest()->getParam('product_id');
        $parentId = (int)$this->getRequest()->getParam('parent_id');
        $backUrl = (int)$this->getRequest()->getParam('backurl');
        $backProductUrl = $backUrl;

        if (!$productId) {
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        try {
            $product = $this->product->create()->load($productId);
            $backProductUrl = $product->getUrlInStore();
            if ($parentId && $productId != $parentId) {
                $parent = $this->product->create()->load($parentId);
                $backProductUrl = $parent->getUrlInStore();
            }

            if (!$product->isVisibleInCatalog()) {
                throw new NoSuchEntityException(__('The product is not visible now.'));
            }

            $websiteId = $this->store->getStore()->getWebsiteId();

            $model = $this->doLoadModel($product->getId(), $websiteId);
            if ($model->getId()) {
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You will no longer receive price alerts for this product.'));
            } else {
                $this->messageManager->addWarningMessage(__('Delete failed. It is possible that the price notification has been previously deleted.'));
            }

        } catch (\LogicException $exception) {
            $this->messageManager->addErrorMessage(__('The product was not found.'));
            $resultRedirect->setPath('customer/account/');
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the alert subscription right now.'));
        }
        if ($backUrl) {
            $resultRedirect->setUrl($this->_url->getUrl("productstockalert/pricealert"));
            return $resultRedirect;
        }
        $resultRedirect->setUrl($backProductUrl);
        return $resultRedirect;
    }

    /**
     * Before load model
     *
     * @param string $productId
     * @param string $websiteId
     * @return \Bss\ProductStockAlert\Model\Stock
     * @throws NoSuchEntityException
     */
    private function doLoadModel($productId, $websiteId)
    {
        if ($this->customerSession->getCustomerId()) {
            return $this->loadModel(
                $this->customerSession->getCustomerId(),
                $productId,
                $websiteId
            );
        }
        return $this->loadGuestModel(
            $productId,
            $websiteId
        );
    }

    /**
     * Load model customer
     *
     * @param string $customerId
     * @param string $productId
     * @param string $websiteId
     * @return mixed
     * @throws NoSuchEntityException
     */
    private function loadModel($customerId, $productId, $websiteId)
    {
        return $this->modelStock
            ->setCustomerId($customerId)
            ->setProductId($productId)
            ->setWebsiteId($websiteId)
            ->setStoreId($this->store->getStore()->getId())
            ->loadByParam();
    }

    /**
     * Load model guest
     *
     * @param string $productId
     * @param string $websiteId
     * @return mixed
     * @throws NoSuchEntityException
     */
    private function loadGuestModel($productId, $websiteId)
    {
        $notify = $this->customerSession->getNotifyPriceSubscription();
        $email = $notify[$productId]['email'];

        $model = $this->modelStock
            ->setCustomerEmail($email)
            ->setProductId($productId)
            ->setWebsiteId($websiteId)
            ->setStoreId($this->store->getStore()->getId())
            ->loadByParamGuest();

        unset($notify[$productId]);
        $this->customerSession->setNotifyPriceSubscription($notify);
        return $model;
    }
}
