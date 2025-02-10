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
 * @package    Bss_CoreApi
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Bss\CoreApi\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;

/**
 * Customers field resolver, used for GraphQL request processing.
 */
class RelatedProducts implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $productRepository;

    /**
     * RelatedProducts constructor.
     * @param ValueFactory $valueFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        ValueFactory $valueFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductRepository $productRepository
    )
    {
        $this->valueFactory = $valueFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value
     * @throws GraphQlNoSuchEntityException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value
    {
        $imagePath = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
        try {
            $data = [];
            if (!empty($args['product_ids'])) {
                foreach ($args['product_ids'] as $k => $id) {
                    $product = $this->productRepository->getById($id);
                    $data[$k]['main_product'] = $id;
                    $relatedProducts = $this->getCustomRelatedProductCollection($product);
                    foreach ($relatedProducts as $related) {
                        $data[$k]['related'][] = [
                            'name' => $related->getName(),
                            'image' => $imagePath . $related->getThumbnail(),
                            'link' => $related->getProductUrl(),
                            'sku' => $related->getSku()
                        ];
                    }
                }
            }

            $result = function () use ($data) {
                return !empty($data) ? $data : [];
            };

            return $this->valueFactory->create($result);

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    protected function getCustomRelatedProductCollection($product)
    {
        $collection = $product->getLinkInstance()->useRelatedLinks()
            ->getProductCollection()
            ->setIsStrongMode();
        $collection->setProduct($product);
        $collection->addAttributeToSelect(['name', 'thumbnail']);
        return $collection;
    }
}