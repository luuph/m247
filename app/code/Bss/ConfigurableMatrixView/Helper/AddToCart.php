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
namespace Bss\ConfigurableMatrixView\Helper;

/**
 * Class AddToCart
 *
 * @package Bss\ConfigurableMatrixView\Helper
 */
class AddToCart
{
    protected $escaper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Form Key Validator
     *
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * AddToCart constructor.
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     */
    public function __construct(
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        $this->escaper = $escaper;
        $this->productFactory = $productFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * Format Message
     *
     * @param string $message
     * @return array|string
     */
    public function formatMessage($message)
    {
        return $this->escaper->escapeHtml($message);
    }

    /**
     * Get Product
     *
     * @param int $storeId
     * @param int $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($storeId, $productId)
    {
        return $this->productFactory->create()->setStoreId($storeId)->load($productId);
    }

    /**
     * Is Redirect to cart after add to cart
     *
     * @return bool
     */
    public function isRedirectToCart()
    {
        $shouldRedirectToCart = $this->scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $shouldRedirectToCart;
    }

    /**
     * Get Store Manager
     *
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Validate Form Key
     *
     * @param array $request
     * @return bool
     */
    public function validateFormKey($request)
    {
        return $this->formKeyValidator->validate($request);
    }
}
