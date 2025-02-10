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
namespace Bss\ProductStockAlert\Controller\Cart;

use Magento\Framework\App\Action\Context;

class AddToCart extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * Construct.
     *
     * @param Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->product = $product;
        $this->redirect = $redirect;
        $this->url = $url;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Add to cart with email.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|(\Magento\Framework\Controller\Result\Redirect&\Magento\Framework\Controller\ResultInterface)|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        $productId = (int)$this->getRequest()->getParam('product_id');
        $params = [
            'form_key' => $this->formKey->getFormKey(),
            'product' => $productId,
            'qty' => 1
        ];
        $product = $productId ? $this->product->load($productId) : null;

        if (!$product) {
            $this->messageManager->addWarningMessage(__("Product ID is incorrect."));
            $resultRedirect->setUrl($this->url->getUrl('noroute'));
            return $resultRedirect;
        }

        try {
            $this->cart->addProduct($product, $params);
            $this->cart->save();

            $this->messageManager->addSuccessMessage(__('You added %1 to your shopping cart.', $product->getName()));
            $this->messageManager->addNoticeMessage(__('The price of this product has been changed. If the change is not visible, try logging in or making changes on the product page.'));
            $resultRedirect->setUrl($this->url->getUrl('checkout/cart', ['_secure' => true]));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            $resultRedirect->setUrl($product->getUrlInStore());
        }

        return $resultRedirect;
    }
}
