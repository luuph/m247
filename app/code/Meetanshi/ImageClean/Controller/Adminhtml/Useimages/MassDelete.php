<?php

namespace Meetanshi\ImageClean\Controller\Adminhtml\Useimages;

use Meetanshi\ImageClean\Model\Imageclean;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Meetanshi\ImageClean\Model\ResourceModel\UseImages\CollectionFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

class MassDelete extends Action
{
    protected $messageManager;
    protected $filter;
    protected $collectionFactory;
    protected $imageClean;
    protected $file;
    protected $fileSystem;
    protected $productFactory;
    protected $productRepository;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Imageclean $imageclean,
        File $file,
        Filesystem $filesystem,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->imageClean = $imageclean;
        $this->file = $file;
        $this->fileSystem = $filesystem;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->count();

            $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
            $mediaRootDir = $mediaDirectory->getAbsolutePath('catalog/product');

            foreach ($collection as $item) {
                $id = $item['imageclean_id'];
                $row = $this->imageClean->load($id);

                if ($item['product_id'] > 0) {
                    $product = $this->productFactory->create();
                    $existingMediaGalleryEntries = $product->load($item['product_id'])->getMediaGalleryEntries();
                    foreach ($existingMediaGalleryEntries as $key => $entry) {
                        $fileName = $item['filename'];
                        if ($entry->getData('file') == $fileName) {
                            unset($existingMediaGalleryEntries[$key]);

                            $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                            $this->productRepository->save($product);
                        }
                    }
                }
                if ($this->file->isExists($mediaRootDir .$row->getFilename())) {
                    $this->file->deleteFile($mediaRootDir . $row->getFilename());
                }
                $row->delete();
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/useimages/index');
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_ImageClean::imageclean');
    }
}
