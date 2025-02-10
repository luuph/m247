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
 * @package   Bss_ProductTags
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Controller\Adminhtml\Tag;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Bss\ProductTags\Model\ResourceModel\ProTags\CollectionFactory;

class MassDisable extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Bss\ProductTags\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * MassDisable constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Bss\ProductTags\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Bss\ProductTags\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_ProductTags::mass_disable_tag');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $id = [];
            foreach ($collection as $item) {
                $item->setStatus(0);
                $id[] = $item->getProtagsId();
                $this->saveItem($item);

            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/');
        }

        $productId = $this->productCollectionFactory->create()
            ->addFieldToSelect('product_id')
            ->addFieldToFilter('protags_id', ['in' => $id])
            ->getData();
        $productIds = [];
        foreach ($productId as $value) {
            $productIds[] = $value['product_id'];
        }

        $indexer = $this->indexerRegistry->get(\Bss\ProductTags\Model\Indexer\Protag::INDEXER_ID);
        $indexer->reindexList($productIds);
        $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been disabled.', $collection->getSize())
        );

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param object $item
     * @return mixed
     */
    private function saveItem($item)
    {
        return $item->save();
    }
}
