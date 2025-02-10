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
declare(strict_types=1);

namespace Bss\ProductGridInlineEditor\Controller\Adminhtml\Source;

use Magento\Backend\App\Action;
use Magento\Catalog\Model\Indexer\Product\Full;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DB\Select;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;

/**
 * Product grid inline edit controller
 *
 */
class InlineEdit extends Action
{
    const ADMIN_RESOURCE = 'Magento_InventoryApi::source';

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemFactory;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Full
     */
    private $indexer;

    /**
     * constructor
     *
     * @param Action\Context $context
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param CollectionFactory $productCollectionFactory
     * @param Full $indexer
     */
    public function __construct(
        Action\Context $context,
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemInterfaceFactory $sourceItemFactory,
        CollectionFactory $productCollectionFactory,
        Full $indexer
    ) {
        parent::__construct($context);
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->indexer = $indexer;
    }

    /**
     * Execute method
     *
     * @return \Magento\Framework\App\ResponseInterface|Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!$this->getRequest()->getParam('isAjax') || empty($postItems)) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }
        $sourceItems = [];
        if (is_array($postItems)) {
            $mapping = [];
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->getSelect()
                ->reset(Select::COLUMNS)
                ->columns(['entity_id', 'sku']);
            if (isset(end($postItems)['product_id'])) {
                $productIds = [end($postItems)['product_id']];
            } else {
                $productIds = array_keys($postItems);
            }
            $productCollection->addFieldToFilter('entity_id', $productIds);

            foreach ($productCollection->getData() as $item) {
                $mapping[$item['entity_id']] = $item['sku'];
            }

            foreach ($postItems as $entityId => $data) {
                if (isset($data['product_id'])) {
                    $entityId = $data['product_id'];
                }
                if (!isset($data['source_code']) || !isset($mapping[$entityId])) {
                    continue;
                }
                $model = $this->sourceItemFactory->create();
                $model->setStatus(isset($data['source_item_status']) ? (int)$data['source_item_status'] : 1);
                $model->setSourceCode($data['source_code']);
                $model->setQuantity(isset($data['quantity']) ? (float)$data['quantity'] : 0);
                $model->setSku($mapping[$entityId]);

                $this->indexer->executeRow($entityId);

                $sourceItems[] = $model;
            }
        }

        if (!empty($sourceItems)) {
            $this->sourceItemsSave->execute($sourceItems);
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}
