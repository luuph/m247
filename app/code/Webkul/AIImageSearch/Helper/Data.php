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

namespace Webkul\AIImageSearch\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const COLLECTION_NAME = 'wk_ai_image_search_collection';

    /**
     * Dependency Initilization
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Webkul\AIChromaDbClient\Adapter\AdapterFactory $adapterFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        protected \Magento\Catalog\Model\ProductFactory $productFactory,
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
        protected \Magento\Framework\Filesystem\Driver\File $fileDriver,
        protected \Webkul\AIChromaDbClient\Adapter\AdapterFactory $adapterFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Get File In Base64
     *
     * @param string $filePath
     * @return string
     */
    public function getFileInBase64Encode($filePath)
    {
        return base64_encode($this->fileDriver->fileGetContents($filePath));
    }

    /**
     * Get Adapter
     *
     * @return \Webkul\AIChromaDbClient\Adapter\Adapter
     */
    public function getAdapter()
    {
        return $this->adapterFactory->create();
    }

    /**
     * Get Config Values
     *
     * @param string $field
     * @param int $storeId
     * @return string
     */
    public function getConfig($field, $storeId = 0)
    {
        $configValue = $this->scopeConfig->getValue(
            'aichromadbclient/ai_image_search_setting/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $configValue;
    }

    /**
     * Encodes the given $arr array which is encoded in the array format
     *
     * @param  array $arr
     * @return array
     */
    public function jsonEncoder($arr = [])
    {
        return $this->serializer->serialize($arr);
    }

    /**
     * Decode the given $arr array which is encoded in the array format
     *
     * @param string $str
     * @return array
     */
    public function jsonDecode($str)
    {
        return $this->serializer->unserialize($str);
    }

    /**
     * Generate Embeddings
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Webkul\AIChromaDbClient\Adapter\Adapter $adapter
     * @param string $collectionId
     * @param string $parentId
     * @return void
     */
    public function generateEmbeddings($product, $adapter, $collectionId, $parentId)
    {
        $images = $product->getMediaGalleryImages();
        if ($images->getSize()) {
            foreach ($images as $child) {
                $imageUrl = $child->getUrl();
                $imageValueId = $child->getValueId();
                $imageInBase64 = $this->getFileInBase64Encode($imageUrl);
                $response = $adapter->createImageEmbeddings($imageInBase64);
                $embedding = [];
                if (!empty($response['data'][0]['embedding'])) {
                    $embedding = $response['data'][0]['embedding'];
                    $adapter->upsertCollectionItems(
                        $collectionId,
                        [
                            'embeddings' => [$embedding],
                            'ids' => ['p-' . $parentId . '_im-' . $imageValueId],
                            'metadatas' => [
                                [
                                    'id' => 'p-' . $parentId . '_im-' . $imageValueId,
                                    'product_id' => $parentId,
                                    'name' => $product->getName(),
                                    'sku' => $product->getSku(),
                                    'url_key' => $product->getUrlKey()
                                ]
                            ]
                        ]
                    );
                }
            }
        }
    }

    /**
     * Generate Product Image Embeddings
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Webkul\AIChromaDbClient\Adapter\Adapter $adapter
     * @param string $collectionId
     * @param string $parentId
     * @param int $parent
     * @return void
     */
    public function generateProductImageEmbeddings($product, $adapter, $collectionId, $parentId, $parent)
    {
        if ($parent == 1) {
            $this->deleteProductEmbeddings($adapter, $collectionId, $product->getEntityId(), $parentId);
        } else {
            $this->generateEmbeddings($product, $adapter, $collectionId, $parentId);
        }
    }

    /**
     * Delete Product Image Embeddings
     *
     * @param \Webkul\AIChromaDbClient\Adapter\Adapter $adapter
     * @param string $collectionId
     * @param string $productId
     * @param string $currentId
     * @return void
     */
    public function deleteProductImageEmbeddings($adapter, $collectionId, $productId, $currentId)
    {
        $this->deleteProductEmbeddings($adapter, $collectionId, $productId, $currentId);
    }

    /**
     * Delete Product Embeddings
     *
     * @param \Webkul\AIChromaDbClient\Adapter\Adapter $adapter
     * @param string $collectionId
     * @param int $productId
     * @param int $parentId
     * @return void
     */
    public function deleteProductEmbeddings($adapter, $collectionId, $productId, $parentId)
    {
        $product = $this->productFactory->create()->load($productId);
        $images = $product->getMediaGalleryImages();
        $where = null;
        if ($images->getSize()) {
            foreach ($images as $child) {
                if ($images->getSize() > 1) {
                    $where['$or'][]['id'] = ['$eq' => "p-$parentId"."_im-".$child->getValueId()];
                } else {
                    $where['id'] = ['$eq' => "p-$parentId"."_im-".$child->getValueId()];
                }
            }
        }
        if (!empty($where)) {
            $adapter->deleteCollectionItems(
                $collectionId,
                [
                    'where' => $where
                ]
            );
        }
    }
}
