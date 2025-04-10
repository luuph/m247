<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AIImageSearch
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AIImageSearch\Plugin;

class Product
{
    /**
     * Constructor
     *
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $configurable
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\AIImageSearch\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        private \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $configurable,
        private \Magento\Framework\Message\ManagerInterface $messageManager,
        private \Magento\Catalog\Model\ProductFactory $productFactory,
        private \Webkul\AIImageSearch\Helper\Data $helper,
        private \Psr\Log\LoggerInterface $logger
    ) {
    }

    /**
     * After product save
     *
     * @param \Magento\Catalog\Model\Product $subject
     * @param \Magento\Catalog\Model\Product $result
     * @return \Magento\Catalog\Model\Product
     */
    public function afterSave(\Magento\Catalog\Model\Product $subject, $result)
    {
        try {
            $collectionName = $this->helper::COLLECTION_NAME;
            $adapter = $this->helper->getAdapter();
            $adapter->intializeClients();
            $adapter->createCollection($collectionName);
            $collection = $adapter->getCollection($collectionName);
            $collectionId = $collection['id'];
            $productId = $subject->getEntityId();
            $parentProductIds = $this->configurable->create()->getParentIdsByChild($productId);
            if (empty($parentProductIds)) {
                $this->regenerateProductImageEmbeddings(
                    $subject,
                    $adapter,
                    $collectionId,
                    $productId
                );
            } else {
                $this->regenerateProductImageEmbeddings(
                    $subject,
                    $adapter,
                    $collectionId,
                    $productId
                );
                foreach ($parentProductIds as $parentId) {
                    $product = $this->productFactory->create()->load($parentId);
                    $this->regenerateProductImageEmbeddings(
                        $product,
                        $adapter,
                        $collectionId,
                        $parentId
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug(
                "Webkul_AIImageSearch::Product Save After: " . $e->getMessage()
            );
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $result;
    }

    /**
     * Regenerate Product Image Embeddings
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Webkul\AIChromaDbClient\Adapter\Adapter $adapter
     * @param string $collectionId
     * @param string $productId
     * @return void
     */
    public function regenerateProductImageEmbeddings($product, $adapter, $collectionId, $productId)
    {
        if ($product->getVisibility() == 3 || $product->getVisibility() == 4) {
            if ($product->getTypeId() == 'configurable') {
                $this->helper->generateProductImageEmbeddings(
                    $product,
                    $adapter,
                    $collectionId,
                    $productId,
                    1
                );
                $productTypeInstance = $product->getTypeInstance();
                $usedProducts = $productTypeInstance->getUsedProducts($product);
                foreach ($usedProducts as $childProduct) {
                    $this->helper->generateProductImageEmbeddings(
                        $childProduct,
                        $adapter,
                        $collectionId,
                        $productId,
                        0
                    );
                }
            } else {
                $this->helper->generateProductImageEmbeddings(
                    $product,
                    $adapter,
                    $collectionId,
                    $productId,
                    0
                );
            }
        } else {
            $this->helper->deleteProductImageEmbeddings(
                $adapter,
                $collectionId,
                $productId,
                $productId
            );
        }
    }
}
