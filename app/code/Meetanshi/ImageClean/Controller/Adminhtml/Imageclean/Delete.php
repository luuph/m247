<?php

namespace Meetanshi\ImageClean\Controller\Adminhtml\Imageclean;

use Meetanshi\ImageClean\Model\ImagecleanFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var ImagecleanFactory
     */
    protected $modelImagecleanFactory;
    protected $file;
    protected $fileSystem;
    protected $productFactory;
    protected $imageProcessor;
    protected $productGallery;

    public function __construct(
        Context $context,
        ImagecleanFactory $modelImagecleanFactory,
        File $file,
        Filesystem $filesystem,
        ProductFactory $productFactory,
        Processor $processor,
        Gallery $gallery
    ) {

        $this->modelImagecleanFactory = $modelImagecleanFactory;
        $this->file = $file;
        $this->fileSystem = $filesystem;
        $this->productFactory = $productFactory;
        $this->imageProcessor = $processor;
        $this->productGallery = $gallery;
        parent::__construct($context);
    }

    public function execute()
    {
        if ($this->getRequest()->getParam('imageclean_id') > 0) {
            try {
                $model = $this->modelImagecleanFactory->create();
                $model->load($this->getRequest()->getParam('imageclean_id'));
                $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
                $mediaRootDir = $mediaDirectory->getAbsolutePath('catalog/product');

                if ($this->file->isExists($mediaRootDir .$model->getFilename()))  {
                    $this->file->deleteFile($mediaRootDir . $model->getFilename());
                }
                $model->setId($this->getRequest()->getParam('imageclean_id'))->delete();
                $this->messageManager->addSuccess(__('Image was successfully deleted'));
                $this->_redirect('*/images/index');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/images/index');
            }
        }
        $this->_redirect('*/images/index');
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_ImageClean::imageclean');
    }
}