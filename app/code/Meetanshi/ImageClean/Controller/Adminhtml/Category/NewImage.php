<?php

namespace Meetanshi\ImageClean\Controller\Adminhtml\Category;

use Meetanshi\ImageClean\Helper\Data as HelperData;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Meetanshi\ImageClean\Model\ImagecleanFactory;
use Meetanshi\ImageClean\Model\ResourceModel\Imageclean\CollectionFactory as ImageCollection;

class NewImage extends Action
{
    /**
     * @var HelperData
     */
    protected $helperData;
    private $directoryList;
    private $modelImagecleanFactory;
    protected $imageCollectionFactory;

    public function __construct(
        Context $context,
        HelperData $helperData,
        DirectoryList $directoryList,
        ImagecleanFactory $modelImagecleanFactory,
        ImageCollection $imageCollectionFactory
    ) {
    
        $this->helperData = $helperData;
        $this->modelImagecleanFactory = $modelImagecleanFactory;
        $this->directoryList = $directoryList;
        $this->imageCollectionFactory = $imageCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $imageCleanImages = $this->helperData->getCategoryImages();
        $maxImages = $this->helperData->getMaxImages();
        $rootPath = $this->directoryList->getRoot();
        $pathToDirectory = $rootPath . DIRECTORY_SEPARATOR . 'pub' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'category';
        $allImagesInDirectory = $this->helperData->listDirectoriesCategory($pathToDirectory);
        $model = $this->modelImagecleanFactory->create();
        $counter = 0;
        $imgPath = $pathToDirectory;

        $modeldb = $this->imageCollectionFactory->create()
            ->addFieldToFilter('isproduct', array('eq' => '0'));

        $imgClean = [];
        foreach ($modeldb as $image) {
            $imgClean[] = $image->getFilename();
        }

        if (sizeof($allImagesInDirectory) > 0 && sizeof($imageCleanImages) > 0) {
            foreach ($allImagesInDirectory as $item) {
                if ($counter > $maxImages) {
                    break;
                }
                try {
                    $item = strtr($item, '\\', '/');
                    $size = round(filesize($imgPath . $item) / 1024, 2);
                    if (!in_array($item, $imageCleanImages) && !in_array($item,$imgClean)) {
                        $this->helperData->valdir[]['filename'] = $item;
                        $model->setData(['filename' => $item])->setId(null);
                        $model->setIsproduct(0);
                        $model->setPath($item);
                        $model->setSize($size);
                        $model->save();

                        $counter++;
                    }
                } catch (\Exception $e) {
                    $this->helperData->logger->info($e->getMessage());
                }
            }
        }
        $this->_redirect('*/images/category');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_ImageClean::imageclean');
    }
}
