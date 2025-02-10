<?php

namespace Meetanshi\ImageClean\Helper;

use Meetanshi\ImageClean\Model\ImagecleanFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DB\Exception;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Meetanshi\ImageClean\Model\ResourceModel\Imageclean\CollectionFactory as ImageCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Meetanshi\ImageClean\Model\Imageclean;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

class Data extends AbstractHelper
{
    const RESOURCES = 'image_clean/general/resources';
    const MAX_IMAGES = 'image_clean/general/max_images';
    const START_TIME = 'image_clean/general/time';
    const SCHEDULE = 'image_clean/general/enable_schedule';
    const FREQUENCY = 'image_clean/general/frequency';

    protected $modelImagecleanFactory;
    protected $categoryCollectionFactory;
    protected $storeManager;
    protected $imageCleanFactory;
    protected $result = [];
    protected $mainTable;
    public $valdir = [];
    protected $collectionFactory;
    protected $imageCollectionFactory;
    public $logger;
    private $file;
    private $resourceConnection;
    private $filesystem;
    private $directoryList;
    protected $productFactory;
    protected $productRepository;
    protected $imageClean;
    protected $imageProcessor;
    protected $productGallery;
    public $dateTime;
    protected $state;

    public function __construct(
        Context $context,
        CollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $StoreManagerInterface,
        ImagecleanFactory $modelImagecleanFactory,
        ImageCollection $imageCollectionFactory,
        ResourceConnection $resourceConnection,
        Filesystem $filesystem,
        DirectoryList $directoryList,
        File $file,
        LoggerInterface $logger,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        Imageclean $imageclean,
        Processor $processor,
        Gallery $gallery,
        DateTime $dateTime,
        State $state
    )
    {
        $this->modelImagecleanFactory = $modelImagecleanFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $StoreManagerInterface;
        $this->imageCollectionFactory = $imageCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->logger = $logger;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->imageClean = $imageclean;
        $this->imageProcessor = $processor;
        $this->productGallery = $gallery;
        $this->dateTime = $dateTime;
        $this->state = $state;
        parent::__construct($context);
    }

    public function getFrequency(){
        return $this->scopeConfig->getValue(self::FREQUENCY, ScopeInterface::SCOPE_STORE);
    }
    public function getMaxImages(){
        return $this->scopeConfig->getValue(self::MAX_IMAGES,ScopeInterface::SCOPE_STORE);
    }
    public function getStartTime(){
        return $this->scopeConfig->getValue(self::START_TIME, ScopeInterface::SCOPE_STORE);
    }
    public function isEnableSchedule(){
        return $this->scopeConfig->getValue(self::SCHEDULE, ScopeInterface::SCOPE_STORE);
    }
    public function getDateTime(){
        return $this->dateTime;
    }

    public function clearUsedImages(){
        try {
            $maxImages = $this->getMaxImages();
            $modeldb = $this->imageCollectionFactory->create()
                ->addFieldToFilter('isproduct', array('eq' => '1'))
                ->addFieldToFilter('used', array('eq' => '1'));

            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $mediaRootDir = $mediaDirectory->getAbsolutePath('catalog/product');
            $counter = 0;
            foreach ($modeldb as $image) {
                if ($image->getProductId() > 0) {
                    $product = $this->productFactory->create()->load($image->getProductId());
                    $images = $product->getMediaGalleryImages();
                    foreach($images as $child) {
                        $this->productGallery->deleteGallery($child->getValueId());
                        $this->imageProcessor->removeImage($product, $child->getFile());
                    }
                }
                if ($this->file->isExists($mediaRootDir . $image->getFilename())) {
                    $this->file->deleteFile($mediaRootDir . $image->getFilename());
                }
                $image->delete();
                $counter++;
                if ($counter > $maxImages){
                    break;
                }
            }
            $this->dbRecordClear();
        }catch (\Exception $e){
            $this->logger->info($e->getMessage());
        }
    }

    public function ClearUnusedImages($type)
    {
        try {
            $maxImages = $this->getMaxImages();

            if ($type == 'category') {
                $modeldb = $this->imageCollectionFactory->create()
                    ->addFieldToFilter('isproduct', array('eq' => '0'));

                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $mediaRootDir = $mediaDirectory->getAbsolutePath('catalog/category');
                $counter = 0;
                foreach ($modeldb as $image) {
                    if ($this->file->isExists($mediaRootDir . $image->getFilename())) {
                        $this->file->deleteFile($mediaRootDir . $image->getFilename());
                    }
                    $image->delete();
                    $counter++;
                    if ($counter > $maxImages){
                        break;
                    }
                }
            } elseif ($type == 'product') {
                $modeldb = $this->imageCollectionFactory->create()
                    ->addFieldToFilter('isproduct', array('eq' => '1'))
                    ->addFieldToFilter('used', array('eq' => '0'));

                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $mediaRootDir = $mediaDirectory->getAbsolutePath('catalog/product');
                $counter = 0;
                foreach ($modeldb as $image) {
                    if ($this->file->isExists($mediaRootDir . $image->getFilename())) {
                        $this->file->deleteFile($mediaRootDir . $image->getFilename());
                    }
                    $image->delete();
                    $counter++;
                    if ($counter > $maxImages){
                        break;
                    }
                }
            }
            //$this->dbRecordClear();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    public function dbRecordClear($counter = 0){
        try{
            $maxImages = $this->getMaxImages();
            $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $connection = $this->resourceConnection->getConnection('core_write');
            $tableName = $this->resourceConnection->getTableName('catalog_product_entity_media_gallery');
            $result = $connection->fetchAll('SELECT * FROM ' . $tableName . ' where media_type = "image"');
            foreach ($result as $item) {
                $imagePath = $mediaDir->getAbsolutePath('catalog/product' . $item['value']);
                if (!$mediaDir->isExist($imagePath)) {
                    $sql = "Delete FROM " . $tableName . " Where value_id = " . $item['value_id'];
                    $connection->query($sql);
                }
                $counter++;
                if ($counter > $maxImages){
                    break;
                }
            }
        }catch (\Exception $e){
            $this->logger->info($e->getMessage());
        }
    }

    public function ConfigClear()
    {
        $resource = $this->scopeConfig->getValue(self::RESOURCES, ScopeInterface::SCOPE_STORE);
        $resources = explode(",", $resource);
        $maxImages = $this->getMaxImages();
        $counter = 0;
        try {
            if (in_array("unusedProduct", $resources)) {
                $modeldb = $this->imageCollectionFactory->create()
                    ->addFieldToFilter('isproduct', array('eq' => '1'))
                    ->addFieldToFilter('used', array('eq' => '0'));

                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $mediaRootDir = $mediaDirectory->getAbsolutePath('catalog/product');
                foreach ($modeldb as $image) {
                    if ($this->file->isExists($mediaRootDir . $image->getFilename())) {
                        $this->file->deleteFile($mediaRootDir . $image->getFilename());
                    }
                    $image->delete();
                    $counter++;
                    if ($counter > $maxImages){
                        break;
                    }
                }
            }

            if (in_array("unusedCategory", $resources)) {
                $modeldb = $this->imageCollectionFactory->create()
                    ->addFieldToFilter('isproduct', array('eq' => '0'));

                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $mediaRootDir = $mediaDirectory->getAbsolutePath('catalog/category');
                foreach ($modeldb as $image) {
                    if ($this->file->isExists($mediaRootDir . $image->getFilename())) {
                        $this->file->deleteFile($mediaRootDir . $image->getFilename());
                    }
                    $image->delete();
                    $counter++;
                    if ($counter > $maxImages){
                        break;
                    }
                }
            }
            if (in_array("dbRecordProduct", $resources)) {
                $this->dbRecordClear($counter);
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            return false;
        }
        return true;
    }

    public function compareList($type)
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
            $imageCleanImages = $this->modelImagecleanFactory->create()->getCollection()->getImages();

            $rootPath = $this->directoryList->getRoot();
            $pathToDirectory = $rootPath . DIRECTORY_SEPARATOR . 'pub' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';
            $allImagesInDirectory = $this->listDirectories($pathToDirectory);
            $model = $this->modelImagecleanFactory->create();
            $maxImages = $this->getMaxImages();
            $count = 0;

            if ($type == 'used'){
                $modeldb = $this->imageCollectionFactory->create()
                    ->addFieldToFilter('isproduct', array('eq' => '1'))
                    ->addFieldToFilter('used', array('eq' => '1'));

                $imgClean = [];
                foreach ($modeldb as $image) {
                    $imgClean[] = $image->getFilename();
                }

                try {
                    $newColl = $modeldb->addFieldToFilter('product_id', array('null' => true));

                    if (@count($newColl->getData() > 0)) {
                        foreach ($newColl->getData() as $itm) {
                            $deletedProduct = $this->modelImagecleanFactory->create()->load($itm['imageclean_id']);
                            $deletedProduct->setUsed(0);
                            $deletedProduct->save();
                        }
                    }
                }catch (\Exception $e){
                    $this->logger->info($e->getMessage());
                    return;
                }

                if (sizeof($allImagesInDirectory) > 0 && sizeof($imageCleanImages) > 0) {
                    foreach ($allImagesInDirectory as $item) {
                        try {
                            $item = strtr($item, '\\', '/');
                            $size = round(filesize($pathToDirectory . $item) / 1024, 2);

                            if (array_key_exists($item, $imageCleanImages) && !in_array($item,$imgClean)) {
                                $valdir[]['filename'] = $item;
                                $model->setData(['filename' => $item])->setId(null);
                                $model->setIsproduct(1);
                                $model->setUsed($imageCleanImages[$item]['used']);
                                $model->setProductId($imageCleanImages[$item]['productId']);
                                $model->setProductName($imageCleanImages[$item]['product_name']);
                                $model->setPath($item);
                                $model->setSize($size);
                                $model->save();

                                $count++;
                            }
                            if ($count > $maxImages) {
                                break;
                            }
                        } catch (\Exception $e) {
                            $this->logger->info($e->getMessage());
                            return;
                        }
                    }
                }
            }elseif ($type == 'unused'){
                $modeldb = $this->imageCollectionFactory->create()
                    ->addFieldToFilter('isproduct', array('eq' => '1'))
                    ->addFieldToFilter('used', array('eq' => '0'));

                $imgClean = [];
                foreach ($modeldb as $image) {
                    $imgClean[] = $image->getFilename();
                }

                if (sizeof($allImagesInDirectory) > 0 && sizeof($imageCleanImages) > 0) {
                    foreach ($allImagesInDirectory as $item) {
                        try {
                            $item = strtr($item, '\\', '/');
                            $size = round(filesize($pathToDirectory . $item) / 1024, 2);

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
                            if ($count > $maxImages) {
                                break;
                            }
                        } catch (\Exception $e) {
                            $this->logger->info($e->getMessage());
                            return;
                        }
                    }
                }
            }
            return null;
        }catch (\Exception $e){
            $this->logger->info($e->getMessage());
            return;
        }
    }

    public function compareCategoryList()
    {
        $imageCleanImages = $this->getCategoryImages();
        $maxImages = $this->getMaxImages();
        $rootPath = $this->directoryList->getRoot();
        $pathToDirectory = $rootPath . DIRECTORY_SEPARATOR . 'pub' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'category';
        $allImagesInDirectory = $this->listDirectoriesCategory($pathToDirectory);
        $model = $this->modelImagecleanFactory->create();
        $counter = 0;

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
                    $size = round(filesize($pathToDirectory . $item) / 1024, 2);
                    if (!in_array($item, $imageCleanImages) && !in_array($item, $imgClean)) {
                        $valdir[]['filename'] = $item;
                        $model->setData(['filename' => $item])->setId(null);
                        $model->setIsproduct(0);
                        $model->setPath($item);
                        $model->setSize($size);
                        $model->save();

                        $counter++;
                    }
                } catch (\Exception $e) {
                    $this->logger->info($e->getMessage());
                }
            }
        }
        return null;
    }

    public function listDirectories($path)
    {
        $rootsPath = $this->directoryList->getRoot();
        $rootPathLen = strlen($rootsPath);

        if (is_dir($path)) {
            if ($dir = opendir($path)) {
                while (($entry = readdir($dir)) !== false) {
                    if (preg_match('/^\./', $entry) != 1) {
                        if (is_dir($path . DIRECTORY_SEPARATOR . $entry) && !in_array($entry, ['cache', 'watermark', 'placeholder'])) {
                            $this->listDirectories($path . DIRECTORY_SEPARATOR . $entry);
                        } elseif (!in_array($entry, ['cache', 'watermark', 'placeholder']) && (strpos($entry, '.') !== 0)) {
                            $this->result[] = substr($path . DIRECTORY_SEPARATOR . $entry, $rootPathLen + 26);
                        }
                    }
                }
                closedir($dir);
            }
        }
        return $this->result;
    }

    public function listDirectoriesCategory($path)
    {
        $rootsPath = $this->directoryList->getRoot();
        $rootPathLen = strlen($rootsPath);

        if (is_dir($path)) {
            if ($dir = opendir($path)) {
                while (($entry = readdir($dir)) !== false) {
                    if (preg_match('/^\./', $entry) != 1) {
                        if (is_dir($path . DIRECTORY_SEPARATOR . $entry) && !in_array($entry, ['cache', 'watermark', 'placeholder'])) {
                            $this->listDirectoriesCategory($path . DIRECTORY_SEPARATOR . $entry);
                        } elseif (!in_array($entry, ['cache', 'watermark','placeholder']) && (strpos($entry, '.') !== 0)) {
                            $this->result[] = substr($path . DIRECTORY_SEPARATOR . $entry, $rootPathLen + 27);
                        }
                    }
                }
                closedir($dir);
            }
        }
        return $this->result;
    }

    public function getCategoryImages()
    {
        try {
            $images = [];
            $allImages = $this->modelImagecleanFactory->create()->getCollection();

            foreach ($allImages as $item) {
                $images[] = $item->getFilename();
            }
            $categorys = $this->getCategoryCollection();
            foreach ($categorys as $category) {
                if ($category->getImageUrl() != '') {
                    $mediaPath = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/category';
                    $imgUrl = str_replace($mediaPath, "", $category->getImageUrl());
                    $images[] = $imgUrl;
                }
            }
            return $images;
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    public function getCategoryCollection()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        return $collection;
    }
}

