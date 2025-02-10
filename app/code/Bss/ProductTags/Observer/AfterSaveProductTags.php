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
 * @package    Bss_ProductTags
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Observer;

use Magento\Framework\Event\ObserverInterface;

class AfterSaveProductTags implements ObserverInterface
{
    /**
     * @var \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Bss\ProductTags\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Bss\ProductTags\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * AfterSaveProductTags constructor.
     * @param \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory $collectionFactory
     * @param \Bss\ProductTags\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Bss\ProductTags\Helper\Data $helper
     */
    public function __construct(
        \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory $collectionFactory,
        \Bss\ProductTags\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Bss\ProductTags\Helper\Data $helper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->helper = $helper;
    }

    /**
     * After Save Product Tags
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->getConfig('general/enable')) {
            $dataTag = $observer->getProductTag();
            $protagsId = $dataTag['protags_id'];
            $id = $this->productCollectionFactory->create()
                 ->addFieldToSelect('product_id')
                 ->addFieldToFilter('protags_id', ['in' => $protagsId])
                 ->getData();
            $indexer = $this->indexerRegistry->get(\Bss\ProductTags\Model\Indexer\Protag::INDEXER_ID);

            if (!$indexer->isScheduled()) {
                $collection = $this->collectionFactory->create()
                    ->addFieldToSelect('product_id')
                    ->addFieldToFilter('product_id', ['in' => $id]);
                $productId = [];
                foreach ($collection as $value) {
                    $productId[] = (int)$value->getProductId();
                }
                $indexer->reindexAll();
            }
        }
    }
}
