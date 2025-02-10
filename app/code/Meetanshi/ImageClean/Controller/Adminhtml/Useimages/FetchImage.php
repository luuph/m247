<?php

namespace Meetanshi\ImageClean\Controller\Adminhtml\Useimages;

use Meetanshi\ImageClean\Helper\Data as HelperData;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Meetanshi\ImageClean\Model\ImagecleanFactory;
use Meetanshi\ImageClean\Model\ResourceModel\Imageclean\CollectionFactory as ImageCollection;

class FetchImage extends Action
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
            ->addFieldToFilter('used', array('eq' => '1'));

        $imgClean = [];
        foreach ($modeldb as $image) {
            $imgClean[] = $image->getFilename();
        }

        try {
            $newColl = $modeldb->addFieldToFilter('product_id', array('null' => true));
                               //->addFieldToFilter('product_id', array('is' => new \Zend_Db_Expr('null')));

            if (@count($newColl->getData() > 0)) {
                foreach ($newColl->getData() as $itm) {
                    $deletedProduct = $this->modelImagecleanFactory->create()->load($itm['imageclean_id']);
                    $deletedProduct->setUsed(0);
                    $deletedProduct->save();
                }
            }
        }catch (\Exception $e){
            $this->helperData->logger->info($e->getMessage());
        }

        if (sizeof($allImagesInDirectory) > 0 && sizeof($imageCleanImages) > 0) {
            foreach ($allImagesInDirectory as $item) {
                if ($count > $maxImages) {
                    break;
                }
                try {
                    $item = strtr($item, '\\', '/');
                    $size = round(filesize($imgPath.$item)/1024,2);
                    if (array_key_exists($item, $imageCleanImages) && !in_array($item,$imgClean)) {
                        $valdir[]['filename'] = $item;
                        $model->setData(['filename' => $item])->setId(null);
                        $model->setIsproduct(1);
                        $model->setUsed($imageCleanImages[$item]['used']);
                        $model->setProductId($imageCleanImages[$item]['productId']);
                        $model->setPath($item);
                        $model->setSize($size);
                        $model->setProductName($imageCleanImages[$item]['product_name']);
                        $model->save();

                        $count++;
                    }
                } catch (\Exception $e) {
                    $this->helperData->logger->info($e->getMessage());
                }
            }
        }
        $this->_redirect('*/useimages/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_ImageClean::imageclean');
    }
}
