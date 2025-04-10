<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

class Layer extends \Magento\Catalog\Model\Layer
{
    /**
     * CustomCollection variable
     *
     * @var Mixed
     */
    public $customCollection;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param Layer\ContextInterface $context
     * @param Layer\StateFactory $layerStateFactory
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $catalogProduct
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\ContextInterface $context,
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $catalogProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $layerStateFactory,
            $attributeCollectionFactory,
            $catalogProduct,
            $storeManager,
            $registry,
            $categoryRepository,
            $data
        );

        $this->request = $request;
    }

    /**
     * GetProductCollection function
     *
     * @return void
     */
    public function getProductCollection()
    {
        $wholeData = $this->request->getPostValue();
        if (isset($wholeData["custom"]) && $wholeData["customCollection"] == 1) {
            $this->prepareProductCollection($this->customCollection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $this->customCollection;
            return $this->customCollection;
        } else {
            if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
                $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
            } else {
                $collection = $this->collectionProvider->getCollection($this->getCurrentCategory());
                $this->prepareProductCollection($collection);
                $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
            }
            return $collection;
        }
    }
}
