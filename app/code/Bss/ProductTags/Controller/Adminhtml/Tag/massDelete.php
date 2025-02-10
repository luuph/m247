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

use Bss\ProductTags\Helper\Data;
use Bss\ProductTags\Model\ResourceModel\Product\Collection;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Bss\ProductTags\Model\ResourceModel\ProTags\CollectionFactory;
use Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory as ProtagIndexCollection;

class MassDelete extends \Magento\Backend\App\Action
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
     * @var ProtagIndexCollection
     */
    protected $collectionIndexFactory;

    /**
     * @var \Bss\ProductTags\Model\Indexer\Protag
     */
    protected $protagIndex;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * MassDelete constructor.
     * @param Collection $collection
     * @param Data $helper
     * @param Context $context
     * @param Filter $filter
     * @param ProtagIndexCollection $collectionIndexFactory
     * @param \Bss\ProductTags\Model\Indexer\Protag $protagIndex
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Collection $collection,
        Data $helper,
        Context $context,
        Filter $filter,
        ProtagIndexCollection $collectionIndexFactory,
        \Bss\ProductTags\Model\Indexer\Protag $protagIndex,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionIndexFactory = $collectionIndexFactory;
        $this->protagIndex = $protagIndex;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->collection = $collection;
    }

    /**
     * Check permission
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_ProductTags::mass_delete_tag');
    }

    /**
     * Delete Product Tags
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();

            foreach ($collection as $item) {
                $productIds = $this->collection->getProductIdsOfTag($item['protags_id']);
                $this->deleteItem($item);
                $this->_eventManager->dispatch(
                    'product_tag_after_delete',
                    ['product_tag' => $item, 'request' => $this->getRequest(), 'products_id' => $productIds]
                );
                $this->protagIndex->execute($productIds);
            }
            $this->helper->messengerCache();
            $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Delete Item
     *
     * @param object $item
     * @return mixed
     */
    private function deleteItem($item)
    {
        return $item->delete();
    }
}
