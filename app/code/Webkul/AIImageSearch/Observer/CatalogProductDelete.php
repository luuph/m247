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
namespace Webkul\AIImageSearch\Observer;

use Magento\Framework\Event\ObserverInterface;

class CatalogProductDelete implements ObserverInterface
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
     * Product delete before event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $collectionName = $this->helper::COLLECTION_NAME;
            $adapter = $this->helper->getAdapter();
            $adapter->intializeClients();
            $adapter->createCollection($collectionName);
            $collection = $adapter->getCollection($collectionName);
            $collectionId = $collection['id'];
            $productId = $observer->getProduct()->getId();
            $parentProductIds = $this->configurable->create()->getParentIdsByChild($productId);
            if (empty($parentProductIds)) {
                $this->helper->deleteProductImageEmbeddings(
                    $adapter,
                    $collectionId,
                    $productId,
                    $productId
                );
                $product = $observer->getProduct();
                $productTypeInstance = $product->getTypeInstance();
                $classMethods = get_class_methods($productTypeInstance);
                if (in_array('getUsedProducts', $classMethods)) {
                    $usedProducts = $productTypeInstance->getUsedProducts($product);
                    foreach ($usedProducts as $childProduct) {
                        $this->helper->deleteProductImageEmbeddings(
                            $adapter,
                            $collectionId,
                            $childProduct->getEntityId(),
                            $productId
                        );
                    }
                }
            } else {
                foreach ($parentProductIds as $parentId) {
                    $this->helper->deleteProductImageEmbeddings(
                        $adapter,
                        $collectionId,
                        $productId,
                        $parentId
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug(
                "Webkul_AIImageSearch::Product Delete: " . $e->getMessage()
            );
            $this->messageManager->addError(__($e->getMessage()));
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }
}
