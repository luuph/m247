<?php

namespace Meetanshi\ImageClean\Controller\Adminhtml\Imageclean;

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
        $imageCleanImages = $this->modelImagecleanFactory->create()->getCollection()->getImages();

        $rootPath = $this->directoryList->getRoot();
        $pathToDirectory = $rootPath . DIRECTORY_SEPARATOR . 'pub' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';
        $allImagesInDirectory = $this->helperData->listDirectories($pathToDirectory);
        $model = $this->modelImagecleanFactory->create();
        $maxImages = $this->helperData->getMaxImages();
        $count = 0;
        $imgPath = $pathToDirectory;

        $modeldb = $this->imageCollectionFactory->create()
            ->addFieldToFilter('isproduct', array('eq' => '1'))
            ->addFieldToFilter('used', array('eq' => '0'));

        $imgClean = [];
        foreach ($modeldb as $image) {
            $imgClean[] = $image->getFilename();
        }

        if (sizeof($allImagesInDirectory) > 0 && sizeof($imageCleanImages) > 0) {
            foreach ($allImagesInDirectory as $item) {
                if ($count > $maxImages) {
                    break;
                }
                try {
                    $item = strtr($item, '\\', '/');
                    $size = round(filesize($imgPath.$item)/1024,2);
                     if (!array_key_exists($item, $imageCleanImages) && !in_array($item,$imgClean)) {
                        $valdir[]['filename'] = $item;
                        $model->setData(['filename' => $item])->setId(null);
                        $model->setIsproduct(1);
                        $model->setPath($item);
                        $model->setSize($size);
                        $model->setUsed(0);
                        $model->save();

                        $count++;
                    }
                } catch (\Exception $e) {
                    $this->helperData->logger->info($e->getMessage());
                }
            }
        }
        $this->_redirect('*/images/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_ImageClean::imageclean');
    }
}