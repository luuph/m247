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
 * @package    Bss_ConfigurableMatrixView
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableMatrixView\Controller\Cart;

use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Checkout\Model\Cart as CustomerCart;

/**
 * Class Add
 *
 * @package Bss\ConfigurableMatrixView\Controller\Cart
 */
class Add extends \Magento\Framework\App\Action\Action implements ViewInterface
{
    const URL_TYPE_LINK = 'link';

    protected $options = [];

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Bss\ConfigurableMatrixView\Helper\AddToCart
     */
    protected $helperCart;

    /**
     * Add constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     * @param CustomerCart $cart
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Bss\ConfigurableMatrixView\Helper\AddToCart $helperCart
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        CustomerCart $cart,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Bss\ConfigurableMatrixView\Helper\AddToCart $helperCart
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->cart = $cart;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helperCart = $helperCart;
        parent::__construct($context);
    }

    /**
     * Add product to shopping cart action
     */
    public function execute()
    {
        if (!$this->helperCart->validateFormKey($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $params = $this->getRequest()->getParams();
        $addedProducts = $product_fail = [];
        $productId = (int)$this->getRequest()->getPost('product');
        $productIds = $this->getRequest()->getPost('super_attribute_' . $productId);
        foreach ($productIds as $k => $super_attribute) {
            try {
                $qty = $this->getRequest()->getPost('qty_' . $productId . '_' . $k, 0);
                $product = $this->getProductMTV($productId);
                if ($qty <= 0 || !$product) {
                    continue;
                }

                $paramsr = [];
                $paramsr['product'] = $productId;
                $paramsr['qty']= $qty;
                $paramsr['super_attribute'] = $super_attribute;
                $this->checkAttribute($params, $paramsr);
                $paramsr['options'] = isset($params['options']) ? $params['options'] : [];
                $paramsr['options'] = (!empty($this->options)) ? $this->options : $paramsr['options'];

                $childProductId = $this->getRequest()->getPost('child_product_' . $productId . '_' . $k);
                $childProduct = $this->getProductMTV($childProductId);
                $this->cart->addProduct($product, $paramsr);

                $this->getCustomOption($product);
                $this->returnAddedProduct($addedProducts, $childProduct);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $product_fail = $this->getMessageError($product_fail, $childProduct, $e);

                $cartItem = $this->cart->getQuote()->getItemByProduct($product);
                $this->removeCartItem($cartItem);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t add this item to your shopping cart right now.')
                );
                $this->logger->critical($e);
            }
        }

        $this->saveAdd($addedProducts);
        $url = $this->checkoutSession->getRedirectUrl(true);

        $product_poup['errors'] = $product_fail;
        $product_poup['product_qtys'] = $this->getQtyofProductInCart();
        $fail = empty($product_fail);
        return $this->result($product_poup, $url, $fail);
    }

    /**
     * Return Added Product
     *
     * @param array $addedProducts
     * @param object $childProduct
     */
    public function returnAddedProduct(&$addedProducts, $childProduct)
    {
        if (!$this->checkoutSession->getNoCartRedirect(true)) {
            if (!$this->cart->getQuote()->getHasError()) {
                $addedProducts[] = $childProduct;
            }
        }
    }

    /**
     * Remove Cart Item
     *
     * @param $cartItem
     */
    public function removeCartItem($cartItem)
    {
        if ($cartItem) {
            $this->cart->getQuote()->deleteItem($cartItem);
        }
    }

    /**
     * Return Paramsr
     *
     * @param array $params
     * @param array $paramsr
     */
    public function checkAttribute($params, &$paramsr)
    {
        if (isset($params['super_attribute'])) {
            $paramsr["super_attribute"] += $params['super_attribute'];
        }
    }

    /**
     * Get Custom Option
     *
     * @param object $product
     */
    protected function getCustomOption($product)
    {
        if (empty($this->options)) {
            $cartItem = $this->cart->getQuote()->getItemByProduct($product);
            $this->options = $cartItem->getBuyRequest()->getOptions();
        }
    }

    /**
     * Save product to cart
     *
     * @param array $addedProducts
     */
    protected function saveAdd($addedProducts)
    {
        $related = $this->getRequest()->getParam('related_product');

        if ($addedProducts) {
            try {
                if (!empty($related)) {
                    $this->cart->addProductsByIds(explode(',', $related));
                }

                $this->cart->save()->getQuote()->collectTotals();
                if (!$this->cart->getQuote()->getHasError()) {
                    $products = [];
                    foreach ($addedProducts as $product) {
                        $products[] = '"' . $product->getName() . '"';
                    }

                    $this->messageManager->addSuccessMessage(
                        __(
                            '%1 product(s) have been added to shopping cart: %2.',
                            count($addedProducts),
                            join(', ', $products)
                        )
                    );
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->checkoutSession->getUseNotice(true)) {
                    $this->messageManager->addNoticeMessage(
                        $this->helperCart->formatMessage($e->getMessage())
                    );
                } else {
                    $errormessage = array_unique(explode("\n", $e->getMessage()));
                    $errormessageCart = end($errormessage);
                    $this->messageManager->addErrorMessage(
                        $this->helperCart->formatMessage($errormessageCart)
                    );
                }
                return;
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t add this item to your shopping cart right now.'));
        }
    }

    /**
     * Get product
     *
     * @param int $productId
     * @return \Magento\Catalog\Model\Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getProductMTV($productId)
    {
        $storeId = $this->helperCart->getStoreManager()->getStore()->getId();
        $product = $this->helperCart->getProduct($storeId, $productId);
        return $product;
    }

    /**
     * Resolve response
     *
     * @param array $product_poup
     * @param string $backUrl
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     */
    protected function result($product_poup, $backUrl, $fail)
    {
        if (!$this->getRequest()->isAjax()) {
            return $this->_goBack($backUrl);
        }

        if (($backUrl || $backUrl = $this->getBackUrl()) && $fail) {
            $product_poup['backUrl'] = $backUrl;
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($product_poup);
    }

    /**
     * Resolve response
     *
     * @return int | float
     */
    protected function getQtyofProductInCart()
    {
        $productId = (int)$this->getRequest()->getPost('product');
        $quote = $this->cart->getQuote();

        $items = $quote->getAllVisibleItems();
        $total_qty = 0;
        foreach ($items as $item) {
            if ($item->getProductId() == $productId) {
                $total_qty += $item->getQty();
            }
        }

        return $total_qty;
    }

    /**
     * Set back redirect url to response
     *
     * @param null|string $backUrl
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _goBack($backUrl = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($backUrl || $backUrl = $this->getBackUrl($this->_redirect->getRefererUrl())) {
            $resultRedirect->setUrl($backUrl);
        }
        return $resultRedirect;
    }

    /**
     * Check if URL corresponds store
     *
     * @param string $url
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _isInternalUrl($url)
    {
        if (strpos($url, 'http') === false) {
            return false;
        }

        /**
         * Url must start from base secure or base unsecure url
         */
        /** @var $store \Magento\Store\Model\Store */
        $store = $this->helperCart->getStoreManager()->getStore();
        $unsecure = strpos($url, $store->getBaseUrl()) === 0;
        $secure = strpos($url, $store->getBaseUrl(self::URL_TYPE_LINK, true)) === 0;
        return $unsecure || $secure;
    }

    /**
     * Get resolved back url
     *
     * @param string $defaultUrl
     * @return mixed|string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getBackUrl($defaultUrl = null)
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl && $this->_isInternalUrl($returnUrl)) {
            $this->messageManager->getMessages()->clear();
            return $returnUrl;
        }

        $shouldRedirectToCart = $this->helperCart->isRedirectToCart();

        if ($shouldRedirectToCart || $this->getRequest()->getParam('in_cart')) {
            if ($this->getRequest()->getActionName() == 'add' && !$this->getRequest()->getParam('in_cart')) {
                $this->checkoutSession->setContinueShoppingUrl($this->_redirect->getRefererUrl());
            }
            return $this->_url->getUrl('checkout/cart');
        }

        return $defaultUrl;
    }

    /**
     * Get Error Message
     *
     * @param array $product_fail
     * @param object $childProduct
     * @param object $e
     * @return mixed
     */
    protected function getMessageError($product_fail, $childProduct, $e)
    {
        if ($this->checkoutSession->getUseNotice(true)) {
            $product_fail[$childProduct->getId()] = $e->getMessage();
        } else {
            $messages = array_unique(explode("\n", $e->getMessage()));
            $product_fail[$childProduct->getId()] = end($messages);
        }
        return $product_fail;
    }
}
