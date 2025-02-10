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
 * @copyright Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Controller\Adminhtml\Tag;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Save Tag
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\ProductTags\Model\ProtagsFactory
     */
    protected $protagsFactory;

    /**
     * @var \Bss\ProductTags\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * @var \Bss\ProductTags\Model\Indexer\Protag
     */
    protected $protagIndex;

    /**
     * @var \Bss\ProductTags\Model\ResourceModel\ProTags\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $CategoryCollectionFactory;
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    private $pageCollectionFactory;

    /**
     * @var \Bss\ProductTags\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory
     */
    protected $ProtagIndexCollectionFactory;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\ProductTags\Model\ProtagsFactory $protagsFactory
     * @param \Bss\ProductTags\Model\ResourceModel\ProTags\CollectionFactory $collectionFactory
     * @param \Bss\ProductTags\Helper\Data $helper
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param \Bss\ProductTags\Model\Indexer\Protag $protagIndex
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $CategoryCollectionFactory
     * @param \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageCollectionFactory
     * @param \Bss\ProductTags\Model\ResourceModel\Product\Collection $collection
     * @param \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory $ProtagIndexCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\ProductTags\Model\ProtagsFactory $protagsFactory,
        \Bss\ProductTags\Model\ResourceModel\ProTags\CollectionFactory $collectionFactory,
        \Bss\ProductTags\Helper\Data $helper,
        \Magento\Framework\Filter\FilterManager $filter,
        \Bss\ProductTags\Model\Indexer\Protag $protagIndex,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $CategoryCollectionFactory,
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageCollectionFactory,
        \Bss\ProductTags\Model\ResourceModel\Product\Collection $collection,
        \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory $ProtagIndexCollectionFactory
    ) {
        parent::__construct($context);
        $this->protagsFactory = $protagsFactory;
        $this->helper = $helper;
        $this->filter = $filter;
        $this->protagIndex = $protagIndex;
        $this->collectionFactory = $collectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->CategoryCollectionFactory = $CategoryCollectionFactory;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->collection = $collection;
        $this->ProtagIndexCollectionFactory = $ProtagIndexCollectionFactory;
    }

    /**
     * Check permission
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_ProductTags::save_tag');
    }

    /**
     * Save Product Tags
     *
     * @return ResponseInterface|Redirect|ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $code = $data['name_tag'];
        $tagKeys = $this->filter->translitUrl($data['tag_key']);
        $tagRouter = $this->filter->translitUrl($data['router_tag']);
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->protagsFactory->create();
            $id = $this->getRequest()->getParam('protags_id');

            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(
                        __('The tag was removed by another user or does not exist.')
                    );
                }
            }

            if ($this->checkTagsKey($tagKeys, $id)) {
                $this->messageManager->addErrorMessage(__('Tag Key already exists!'));
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['protags_id' => $this->getRequest()->getParam('protags_id')]
                );
            }
            if (empty($tagRouter)) {
                $data['router_tag'] = "";
            } else {
                if ($this->checkRouter($tagRouter, $id)) {
                    $this->messageManager->addErrorMessage(__('Router already exists!'));
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['protags_id' => $this->getRequest()->getParam('protags_id')]
                    );
                }
            }

            $model->setData($data);
            if (!$this->validatorAttrCode($code)) {
                $this->messageManager->addErrorMessage(
                    __(
                        'Tag keyword(s) "%1" is invalid. Please use only letters (a-z), ' .
                        'numbers (0-9) or underscore(,) and space in this field, first character should be a letter.',
                        $code
                    )
                );
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['protags_id' => $this->getRequest()->getParam('protags_id')]
                );
            }
            if (strlen($tagKeys) > 80) {
                $this->messageManager->addErrorMessage(__('Tag Key needs to be shorter than 80 characters!'));
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['protags_id' => $this->getRequest()->getParam('protags_id')]
                );
            }
            try {
                $model->save();
                $protagIndex = $this->ProtagIndexCollectionFactory->create();

                if (isset($data['bss_input'])) {
                    $dataProducts = $data['bss_input'];
                } elseif (isset($data['products'])) {
                    $dataProducts = $data['products'];
                }

                if (!empty($dataProducts)) {
                    $productId = explode('&', $dataProducts);
                } else {
                    if (!isset($data['protags_id'])) {
                        $data['protags_id'] = $model->getProtagsId();
                    }
                    $productId = $this->collection->getProductIdsOfTag($data['protags_id']);
                }

                if (end($productId) === "on") {
                    array_pop($productId);
                }

                $OldProTagIndex = $protagIndex->addFieldToSelect('product_id')
                    ->addFieldToFilter('tag_key', ['in' => [$data['tag_key'], $tagKeys]]);
                if ($OldProTagIndex->getSize() > 0) {
                    $ids = [
                        "currentProductIds" => $productId,
                        "OldProductIds" => $OldProTagIndex->getData()
                    ];
                } else {
                    $ids = $productId;
                }

                $this->protagIndex->execute($ids);
                $this->helper->messengerCache();
                $this->messageManager->addSuccessMessage(__('You saved this tag.'));
                $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['protags_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->messageManager->addWarningMessage(__('Something went wrong while saving the tag.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['protags_id' => $this->getRequest()->getParam('protags_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Validate tags
     *
     * @param string $attributeCode
     * @return bool
     */
    private function validatorAttrCode($attributeCode)
    {
        if ($attributeCode === null) {
            return false;
        }

        $strlen = strlen($attributeCode);
        $tag = preg_replace('/\s*,\s*/', ',', $attributeCode);
        $tag = preg_replace('/\s+/', '-', $tag);
        if ($strlen > 0 && preg_match("/^[A-z][A-z,0-9-]{0,80}$/", $tag)) {
            return true;
        }
        return false;
    }

    /**
     * Check tag key
     *
     * @param string $key
     * @param int $protagId
     * @return bool
     */
    protected function checkTagsKey($key, $protagId)
    {
        $tag = $this->collectionFactory->create()->addFieldToFilter('tag_key', $key);
        $data = $tag->getData();
        if ($tag->getSize() > 0) {
            $id = $data[0]['protags_id'];
            if ($id !== $protagId) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * Check Router
     *
     * @param string $tagRouter
     * @param int $protagId
     * @return bool
     */
    protected function checkRouter($tagRouter, $protagId)
    {
        $tag = $this->collectionFactory->create()->addFieldToFilter('router_tag', ['eq' => $tagRouter]);
        $data = $tag->getData();
        $countRouter = 0;
        if ($tag->getSize() > 0) {
            $id = $data[0]['protags_id'];
            if ($id !== $protagId) {
                $countRouter++;
            }
        }
        $urlKeyProduct = $this->productCollectionFactory->create()->addFieldToSelect('*');
        foreach ($urlKeyProduct as $item) {
            if ($tagRouter == $item->getUrlKey()) {
                $countRouter++;
            }
        }

        $urlCategory = $this->CategoryCollectionFactory->create()->addFieldToSelect('*');
        foreach ($urlCategory as $item) {
            if ($tagRouter == $item->getUrlKey()) {
                $countRouter++;
            }
        }

        $urlCmsPage = $this->pageCollectionFactory->create()->addFieldToSelect('*');
        foreach ($urlCmsPage as $item) {
            if ($tagRouter == $item->getIdentifier()) {
                $countRouter++;
            }
        }
        if ($countRouter > 0) {
            return true;
        }
        return false;
    }
}
