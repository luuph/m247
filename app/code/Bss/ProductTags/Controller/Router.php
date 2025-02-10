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
 * @category  BSS
 * @package   Bss_ProductTags
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;

/**
 * Class Router
 */
class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\ProductTags\Helper\Data
     */
    protected $productTagHelper;

    /**
     * @var \Bss\ProductTags\Model\ProtagIndexFactory
     */
    protected $protagIndexFactory;

    /**
     * @var \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory
     */
    protected $collectionTagsFactory;

    /**
     * Router constructor.
     *
     * @param ActionFactory $actionFactory
     * @param ResponseInterface $response
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\ProductTags\Helper\Data $productTagHelper
     * @param \Bss\ProductTags\Model\ProtagIndexFactory $protagIndexFactory
     * @param \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory $collectionTagsFactory
     */
    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\ProductTags\Helper\Data $productTagHelper,
        \Bss\ProductTags\Model\ProtagIndexFactory $protagIndexFactory,
        \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory $collectionTagsFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->response = $response;
        $this->storeManager = $storeManager;
        $this->productTagHelper = $productTagHelper;
        $this->protagIndexFactory = $protagIndexFactory;
        $this->collectionTagsFactory = $collectionTagsFactory;
    }

    /**
     * Resolve route
     *
     * @param RequestInterface $request
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        // Fix error 100 match iterations issue
        if ($request->getModuleName() === 'tag') {
            return null;
        }

        // Resolve custom router
        $storeId = $this->storeManager->getStore()->getId();
        $urlKey = trim($request->getPathInfo(), '/');
        $urlValues = explode("/", $urlKey);
        $routerTag[] = $urlValues[0];
        if ($routerTag[0] == 'catalogtags') {
            $routerTag[] = '';
        }
        if (count($urlValues) == 2 && isset($urlValues[1])) {
            $router = $this->collectionTagsFactory->create()
                ->addFieldToFilter('router_tag', ['in' => $routerTag])
                ->addFieldToFilter('tag_key', ['eq' => $urlValues[1]]);
            $collection = $this->protagIndexFactory->create()
                ->getCollection()
                ->addFieldToSelect('product_id')
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('main_table.tag_key', $urlValues[1]);
            if ($router->getSize() > 0 &&
                $collection->getSize() > 0) {
                $params = ['tag' => $urlValues[1]];
                $request->setModuleName('tag');
                $request->setControllerName('product');
                $request->setActionName('view');
                $request->setParams($params);
                return $this->actionFactory->create(Forward::class, ['request' => $request]);
            }
        }

        return null;
    }
}
