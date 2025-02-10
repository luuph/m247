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
 * @package    Bss_ProductGridInlineEditor
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ProductGridInlineEditor\Ui\Component\Listing\Grid;

use Bss\ProductGridInlineEditor\Model\ResourceModel\SourceItemResource;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ProductFactory;

/**
 * Product grid inline Data Provider
 *
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;
    /**
     * @var Collection
     */
    protected $collection;
    /**
     * @var SourceItemResource
     */
    private $sourceItemResource;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param SourceItemResource $sourceItemResource
     * @param ProductFactory $productFactory
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        SourceItemResource $sourceItemResource,
        ProductFactory $productFactory,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->sourceItemResource = $sourceItemResource;
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->productFactory = $productFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $productId = $this->request->getParam('entity_id');
        $sku = $this->getSkuId($productId);
        if (!$sku) {
            return parent::getData();
        }
        $data = parent::getData();
        foreach ($data['items'] as &$item) {
            if (!isset($item['source_code'])) {
                continue;
            }
            $item['sku'] = $sku;
            $item['product_id'] = $productId;
            $itemData = $this->sourceItemResource->getSourceItemData($sku, $item['source_code']);
            $item['quantity'] = isset($itemData['quantity']) ? (float)$itemData['quantity'] : 0;
            $item['source_item_status'] =
                !isset($itemData['status']) ? SourceItemInterface::STATUS_IN_STOCK : $itemData['status'];
        }
        $data['totalRecords'] += 1;
        return $data;
    }
    /**
     * Get sku by Id
     *
     * @return string
     * @param string $productId
     */
    public function getSkuId($productId)
    {
        $product = $this->productFactory->create();
        $productById = $product->load($productId, 'id');
        return $productById->getSku();
    }
}
