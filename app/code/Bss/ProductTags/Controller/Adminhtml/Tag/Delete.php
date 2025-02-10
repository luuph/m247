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

use Bss\ProductTags\Model\ResourceModel\Product\Collection;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Delete Tag
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Delete extends Action
{
    /**
     * @var \Bss\ProductTags\Model\ProtagsFactory
     */
    protected $protagsFactory;

    /**
     * @var \Bss\ProductTags\Model\Indexer\Protag
     */
    protected $protagIndex;

    /**
     * @var \Bss\ProductTags\Helper\Data
     */
    protected $helper;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * Delete constructor.
     * @param Collection $collection
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\ProductTags\Model\ProtagsFactory $protagsFactory
     * @param \Bss\ProductTags\Model\Indexer\Protag $protagIndex
     * @param \Bss\ProductTags\Helper\Data $helper
     */
    public function __construct(
        Collection $collection,
        \Magento\Backend\App\Action\Context $context,
        \Bss\ProductTags\Model\ProtagsFactory $protagsFactory,
        \Bss\ProductTags\Model\Indexer\Protag $protagIndex,
        \Bss\ProductTags\Helper\Data $helper
    ) {
        $this->collection = $collection;
        $this->protagIndex = $protagIndex;
        $this->protagsFactory = $protagsFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Check permission
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_ProductTags::delete_tag');
    }

    /**
     * Delete Product Tags
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('protags_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $productIds = $this->collection->getProductIdsOfTag($id);
                $model = $this->protagsFactory->create();
                $model->load($id);
                $model->delete();
                $this->_eventManager->dispatch(
                    'product_tag_after_delete',
                    ['product_tag' => $model, 'request' => $this->getRequest(), 'products_id' => $productIds]
                );
                $this->protagIndex->execute($productIds);
                $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
                $this->messageManager->addSuccessMessage(__('The tag has been deleted.'));
                $this->helper->messengerCache();
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['protags_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a tag to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
