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
 * @package   Bss_FastOrder
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\FastOrder\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Class AddMultipleProduct
 *
 * @package Bss\FastOrder\Controller\Index
 */
class AddMultipleProduct extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Bss\FastOrder\Helper\ConfigurableProduct
     */
    protected $configurableProductHelper;

    /**
     * @var \Bss\FastOrder\Model\Search\Save
     */
    protected $saveModel;

    /**
     * @var \Bss\FastOrder\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected  $storeManager;

    /**
     * AddMultipleProduct constructor.
     *
     * @param Context $context
     * @param \Bss\FastOrder\Helper\ConfigurableProduct $configurableProductHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Bss\FastOrder\Model\Search\Save $saveModel
     * @param \Bss\FastOrder\Helper\Data $helperData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \Bss\FastOrder\Helper\ConfigurableProduct $configurableProductHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Bss\FastOrder\Model\Search\Save $saveModel,
        \Bss\FastOrder\Helper\Data $helperData,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->configurableProductHelper = $configurableProductHelper;
        $this->productRepository = $productRepository;
        $this->saveModel = $saveModel;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $queryText = $this->getRequest()->getParam('skuList');
        $data = $this->getProductManual($queryText);
        if (count($data) === 0) {
            $this->messageManager->addErrorMessage(__('No Item Imported.'));
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($data);
        return $resultJson;
    }

    /**
     * @param array $productParams
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductManual($productParams)
    {
        $data = [];
        $itemAdded = 0;
        $skuExist = [];
        $formatErrors = [];
        $skuData = [];
        $qtyData = [];
        $skuDisable = [];
        $skuImportSuccess = [];
        $this->getSkuData($productParams, $skuData, $qtyData, $formatErrors);
        $childProductList = [];
        $allProductSearch = $this->saveModel->getAllProductSearch($skuData)->getItems();
        $productCollection = $this->saveModel->getStandardProductCollection($skuData, true);
        $cantAccessProducts = [];
        $storeId = $this->storeManager->getStore()->getId();
        if (is_array($allProductSearch)) {
            foreach ($allProductSearch as $product) {
                $productSku = strtolower($product["sku"]);
                $skuExist[] = $productSku;
            }
        }
        foreach ($productCollection as $product) {
            $productSku = strtolower($product->getSku());
            $this->helperData->getEventManager()->dispatch(
                'bss_fast_order_prepare_product_add',
                [
                    'product' => $product
                ]
            );

            // Catalog permission checking
            if ($product->getCantAccess()) {
                $cantAccessProducts[] = $productSku;
                $productCollection->removeItemByKey($product->getId());
                continue;
            }

            if ($product->getStatus() == Status::STATUS_DISABLED) {
                $skuDisable[] = $productSku;
                $productCollection->removeItemByKey($product->getId());
                continue;
            }
            if (!$product->getIsSalable()) {
                $skuOutOfStock[] = $productSku;
                $productCollection->removeItemByKey($product->getId());
                continue;
            }
            $skuImportSuccess[] = $productSku;
            $isChildProduct = false;
            if (isset($childProductList[$product->getId()])) {
                $parentProduct = $childProductList[$product->getId()];
                $isChildProduct = true;
            } elseif ($parentProductId = $this->configurableProductHelper->getParentProductId($product->getId())) {
                $parentProduct = $this->productRepository->getById($parentProductId);
                $parentProductActiveStores = $parentProduct->getStoreIds();
                $isSalable = true;
                if ($parentProduct->getStatus() == Status::STATUS_DISABLED) {
                    $skuDisable[] = $productSku;
                    $productCollection->removeItemByKey($product->getId());
                    $isSalable = false;
                    continue;
                }
                if (!$parentProduct->getIsSalable()) {
                    $skuOutOfStock[] = $productSku;
                    $productCollection->removeItemByKey($product->getId());
                    $isSalable = false;
                    continue;
                }
                if (in_array($storeId, $parentProductActiveStores) && $isSalable) {
                    $childProducts = $parentProduct->getTypeInstance()->getUsedProducts($parentProduct);
                    foreach ($childProducts as $child) {
                        $childProductList[$child->getId()] = $parentProduct;
                    }
                    $isChildProduct = true;
                }
            }
            if ($isChildProduct) {
                $childData = $this->configurableProductHelper->getChildProductData(
                    $parentProduct,
                    $product
                );
                if (!empty($childData)) {
                    $product->setData($childData->getData());
                    $product->setEntityId($product->getId());
                }
            }
            $qty = 1;
            if (!empty($qtyData[$productSku])) {
                $qty = $qtyData[$productSku];
            }
            $product->setQty($qty);
            $itemAdded++;
        }
        $skuErrors = array_diff($skuData, $skuExist);
        if (!empty($skuImportSuccess)) {
            $data = $productCollection->toArray([
                'name',
                'sku',
                'entity_id',
                'type_id',
                'product_hide_price',
                'product_hide_html',
                'product_thumbnail',
                'product_url',
                'popup',
                'product_price',
                'product_price_amount',
                'product_price_exc_tax_html',
                'product_price_exc_tax',
                'qty',
                'popup_html',
                'configurable_attributes',
                'child_product_id'
            ]);
        }
        if (!empty($cantAccessProducts)) {
            $this->messageManager->addErrorMessage(
                __(
                    "We can't add %1 to list because you have no permission to see its.",
                    implode(', ', $cantAccessProducts)
                )
            );
        }
        if (!empty($formatErrors)) {
            $this->messageManager->addErrorMessage(
                __("%1 do not match or wrong format SKU:Qty", join(',', $formatErrors))
            );
        }

        if (!empty($skuErrors)) {
            $this->messageManager->addErrorMessage(
                __("SKU is not found: %1", join(',', $skuErrors))
            );
        }
        if (!empty($skuDisable)) {
            $this->messageManager->addErrorMessage(
                __("Product SKU is disable: %1", join(',', $skuDisable))
            );
        }
        if (!empty($skuOutOfStock)) {
            $this->messageManager->addErrorMessage(
                __("Product SKU is out of stock: %1", join(',', $skuOutOfStock))
            );
        }

        if ($itemAdded > 0) {
            $this->messageManager->addSuccessMessage(__('List was updated'));
        }
        return $data;
    }

    /**
     * @param $productParams
     * @param $skuData
     * @param $qtyData
     * @param $formatErrors
     */
    private function getSkuData($productParams, &$skuData, &$qtyData, &$formatErrors)
    {
        foreach ($productParams as $productParam) {
            $params = explode(':', $productParam);
            $sku = strtolower($params[0]);
            $sku = ltrim(rtrim($sku));
            if (count($params) != 2) {
                $formatErrors[] = $productParam;
                continue;
            }
            if (!is_numeric($params[1])) {
                $formatErrors[] = $productParam;
                continue;
            }
            $skuData[] = $sku;
            $qtyData[$sku] = ltrim(rtrim($params[1]));
        }
    }
}
