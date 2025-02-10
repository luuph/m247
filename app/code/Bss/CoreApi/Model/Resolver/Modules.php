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
class Modules implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory
     */
    private $linkCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * Modules constructor.
     * @param ValueFactory $valueFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory $linkCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        ValueFactory $valueFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory $linkCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->valueFactory = $valueFactory;
        $this->linkCollectionFactory = $linkCollectionFactory;
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
        try {
            $modules = $this->getModules($args);
            $downloadableLinks = [
                'items' => $modules,
                'count' => count($modules)
            ];
            $result = function () use ($downloadableLinks) {
                return !empty($downloadableLinks) ? $downloadableLinks : [];
            };

            return $this->valueFactory->create($result);

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }
    }

    /**
     * @param array $args
     * @return array
     * @throws NoSuchEntityException
     * @codingStandardsIgnoreStart
     */
    protected function getModules(array $args)
    {
        $downloadableLinks = [];
        $linkCollection = $this->linkCollectionFactory->create()
            ->addTitleToResult()
            ->addFieldToSelect('product_id', 'entity_id');

        if (isset($args['product_id'])) {
            $linkCollection->addFieldToFilter('product_id', $args['product_id']);
        }

        $linkCollection->getSelect()
            ->columns('(select value from catalog_product_entity_varchar as cpev1
            LEFT JOIN eav_attribute as e1 ON cpev1.attribute_id = e1.attribute_id
            WHERE e1.attribute_code = "api_name" AND cpev1.entity_id = main_table.product_id limit 1) as api_name')
            ->columns('(select value from catalog_product_entity_varchar as cpev2
            LEFT JOIN eav_attribute as e2 ON cpev2.attribute_id = e2.attribute_id
            WHERE e2.attribute_code = "user_guide" AND cpev2.entity_id = main_table.product_id limit 1) as user_guide')
            ->columns('(select value from catalog_product_entity_varchar as cpev3
            LEFT JOIN eav_attribute as e3 ON cpev3.attribute_id = e3.attribute_id
            WHERE e3.attribute_code = "name" AND cpev3.entity_id = main_table.product_id limit 1) as product_name')
            ->joinLeft(
                $this->resourceConnection->getTableName('url_rewrite'),
                'main_table.product_id = url_rewrite.entity_id AND url_rewrite.entity_type="product" AND
                     url_rewrite.metadata IS NULL AND url_rewrite.store_id=1',
                'url_rewrite.request_path AS product_url'
            );

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($linkCollection as $product) {

            $productUrl = $this->storeManager->getStore()->getBaseUrl() . $product->getProductUrl();
            if (!$product->getApiName()) continue;

            $indexOfPackage = array_search($product->getApiName(), array_column($downloadableLinks, 'name'));
            if ($indexOfPackage === false) {
                $downloadableLinks[] = [
                    'name' => $product->getApiName(),
                    'product_name' => $product->getProductName(),
                    'product_url' => $productUrl,
                    'entity_id' => $product->getEntityId(),
                    'user_guide' => $product->getUserGuide(),
                    'packages' => [['title' => $product->getTitle()]]
                ];
            } else {
                $downloadableLinks[$indexOfPackage]['packages'][] = ['title' => $product->getTitle()];
            }

        }

        return $downloadableLinks;
    }
    // @codingStandardsIgnoreEnd
}