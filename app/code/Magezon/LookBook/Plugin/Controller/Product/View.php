<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\LookBook\Plugin\Controller\Product;

use \Magento\Framework\Controller\ResultFactory;

class View
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $subjectManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cacheManager;

    /**
     * @var \Magezon\Core\Helper\Data
     */
    protected $coreHelper;

    /**
     * @param ResultFactory                                     $resultFactory  
     * @param \Magento\Framework\Registry                       $coreRegistry   
     * @param \Magento\Framework\ObjectManagerInterface         $subjectManager 
     * @param \Magento\Catalog\Model\ProductFactory             $productFactory 
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency  
     * @param \Magento\Store\Model\StoreManagerInterface        $storeManager   
     * @param \Magento\Framework\View\DesignInterface           $design         
     * @param \Magento\Framework\App\Http\Context               $httpContext    
     * @param \Magento\Framework\App\CacheInterface             $cacheManager   
     * @param \Magezon\Core\Helper\Data                         $coreHelper     
     */
    public function __construct(
        ResultFactory $resultFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\ObjectManagerInterface $subjectManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magezon\Core\Helper\Data $coreHelper
    ) {
        $this->resultFactory   = $resultFactory;
        $this->coreRegistry    = $coreRegistry;
        $this->subjectManager  = $subjectManager;
        $this->productFactory  = $productFactory;
        $this->priceCurrency   = $priceCurrency;
        $this->storeManager    = $storeManager;
        $this->design          = $design;
        $this->httpContext     = $httpContext;
        $this->cacheManager    = $cacheManager;
        $this->coreHelper      = $coreHelper;
    }

    /**
     * Initialize requested product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _initProduct($controller)
    {
        $productId  = (int)$controller->getRequest()->getParam('id');
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productFactory->create();
        return $product->load($productId);
    }

	public function aroundExecute(
		\Magento\Catalog\Controller\Product\View $subject,
		\Closure $proceed
	) {
        $params  = $subject->getRequest()->getParams();
        if (isset($params['mgz_lb'])) {
            $product = $this->_initProduct($subject);
            if ($product->getId()) {
                $html = $this->getHtmlFromCache($subject);
                if ($html) {
                    $data['html'] = $html;
                    $subject->getResponse()->representJson(
                        $this->subjectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($data)
                    );
                    return;
                }
            }
        }

        $result  = $proceed();

        $product = $this->getProduct();
        if (isset($params['mgz_lb']) && $product) {
            $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
            $resultLayout->addHandle('lookbook_popup');
            $resultLayout->addHandle('lookbook_popup' . $product->getTypeId());
            $html = $result->getLayout()->getBlock('ajax.product.info')->toHtml();
            $this->saveToCache($subject, $html);
            $data['html'] = $html;
            $subject->getResponse()->representJson(
                $this->subjectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($data)
            );
            return;
        }

		return $result;
	}

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('current_product');
    }

    /**
     * @return string
     */
    public function getHtmlFromCache($subject)
    {
        $product = $this->_initProduct($subject);

        $html = $this->cacheManager->load($this->getCacheKeyInfo($subject));
        if ($html) {
            $data = $this->coreHelper->unserialize($html);
            return $data['html'];
        }
    }

    public function saveToCache($subject, $html) {
        $this->cacheManager->save(
            $this->coreHelper->serialize(['html' => $html]),
            $this->getCacheKeyInfo($subject),
            [
                \Magento\Catalog\Model\Product::CACHE_TAG,
                \Magento\Framework\App\Cache\Type\Block::CACHE_TAG
            ]
        );
    }

    public function getCacheKeyInfo($subject)
    {
        $cache = [
            'LOOKBOOK_PRODUCT_CACHE_HTML',
            $this->priceCurrency->getCurrency()->getCode(),
            $this->storeManager->getStore()->getId(),
            $this->design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            $subject->getRequest()->getParam('id')
        ];
        return implode('_', $cache);
    }
}