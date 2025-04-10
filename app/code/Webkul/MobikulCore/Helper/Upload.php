<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Helper;

use Webkul\MobikulCore\Helper\Data as HelperData;
use Webkul\MobikulCore\Helper\Catalog as HelperCatalog;

/**
 * Mobikul Helper Upload Class
 */
class Upload extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var HelperData $helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Filesystem\Io\File $ioFile
     */
    protected $ioFile;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList $directory
     */
    protected $directory;

    /**
     * @var HelperCatalog $helperCatalog
     */
    protected $helperCatalog;
    
    /**
     * @var \Webkul\MobikulCore\Model\UserImageFactory $userImageFactory
     */
    protected $userImageFactory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File $fileDriver
     */
    protected $fileDriver;

    /**
     * @var \Magento\Framework\Filesystem\Glob $glob
     */
    protected $glob;

    /**
     * @var \Magento\Framework\File\UploaderFactory $uploader
     */
    protected $uploader;

    /**
     * @var \Magento\Framework\Image\AdapterFactory $imageFactory
     */
    protected $imageFactory;

    /**
     * Construc function for Upload Helper
     *
     * @param HelperData $helper
     * @param HelperCatalog $helperCatalog
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem\DirectoryList $directory
     * @param \Webkul\MobikulCore\Model\UserImageFactory $userImageFactory
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\Filesystem\Glob $glob
     * @param \Magento\Framework\File\UploaderFactory $uploader
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     */
    public function __construct(
        HelperData $helper,
        HelperCatalog $helperCatalog,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\DirectoryList $directory,
        \Webkul\MobikulCore\Model\UserImageFactory $userImageFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Filesystem\Glob $glob,
        \Magento\Framework\File\UploaderFactory $uploader,
        \Magento\Framework\Image\AdapterFactory $imageFactory
    ) {
        $this->ioFile = $ioFile;
        $this->helper = $helper;
        $this->directory = $directory;
        $this->helperCatalog = $helperCatalog;
        $this->userImageFactory = $userImageFactory;
        $this->fileDriver = $fileDriver;
        $this->glob = $glob;
        $this->uploader = $uploader;
        $this->imageFactory = $imageFactory;
        parent::__construct($context);
    }

    /**
     * Function to get Base path of File Directory
     *
     * @param Directory $directory directory
     *
     * @return string
     */
    public function getBasePath($directory = "media")
    {
        return $this->directory->getPath($directory);
    }

    /**
     * Function TO Upload Pictures
     *
     * @param Files   $files      files
     * @param integer $customerId customerId
     * @param string  $name       name
     * @param string  $signal     signal
     *
     * @return void
     */
    public function uploadPicture($files, $customerId, $name, $signal)
    {
        $target = $this->getBasePath("media").DS."mobikul".DS."customerpicture".DS.$customerId.DS.$signal;
        if (isset($files) && count($files) > 0) {
            if ($this->fileDriver->isDirectory($target)) {
                $directories = $this->glob->glob($target."*", GLOB_ONLYDIR);
                foreach ($directories as $dir) {
                    $this->ioFile->rmdir($dir, true);
                }
            }
            if (!$this->fileDriver->isDirectory($target)) {
                $this->ioFile->mkdir($target);
            }
            foreach ($files as $image) {
                if ($image["tmp_name"] != "") {
                    $splitname = explode(".", $image["name"]);
                    $finalTarget = $target.$name.".".end($splitname);
                    $uploader = $this->uploader->create(['fileId' => 'files']);
                    $uploader->save($target, $name.".".end($splitname));
                    $userImageModel = $this->userImageFactory->create();
                    $collection = $userImageModel->getCollection()->addFieldToFilter("customer_id", $customerId);
                    if ($collection->getSize() > 0) {
                        $this->saveCustomerImage($collection, $userImageModel, $signal, $name, $splitname);
                    } else {
                        if ($signal == "banner") {
                            $userImageModel->setBanner($name.".".end($splitname));
                        }
                        if ($signal == "profile") {
                            $userImageModel->setProfile($name.".".end($splitname));
                        }
                        $userImageModel->setCustomerId($customerId)->save();
                    }
                }
            }
        }
    }

    /**
     * SaveCustomerImage function
     *
     * @param mixed $collection
     * @param mixed $userImageModel
     * @param mixed $signal
     * @param mixed $name
     * @param mixed $splitname
     * @return void
     */
    public function saveCustomerImage($collection, $userImageModel, $signal, $name, $splitname)
    {
        foreach ($collection as $value) {
            $loadedUserImageModel = $userImageModel->load($value->getId());
            if ($signal == "banner") {
                $loadedUserImageModel->setBanner($name.".".end($splitname));
            }
            if ($signal == "profile") {
                $loadedUserImageModel->setProfile($name.".".end($splitname));
            }
            $loadedUserImageModel->save();
        }
    }

    /**
     * Function to Resize Images and cache
     *
     * @param integer $width      width
     * @param integer $customerId customerId
     * @param integer $mFactor    mFactor
     * @param integer $signal     signal
     *
     * @return array
     */
    public function resizeAndCache($width = 1000, $customerId = 0, $mFactor = 1, $signal = '')
    {
        $returnArray = [];
        $returnArray["url"] = "";
        $returnArray["success"] = false;
        $returnArray["message"] = "";
        $collection = $this->userImageFactory->create()->getCollection()->addFieldToFilter("customer_id", $customerId);
        $time = time();
        if ($collection->getSize() > 0) {
            foreach ($collection as $value) {
                if ($signal == "banner" && $value->getBanner() != "") {
                    $basePath = $this->getBasePath("media").DS."mobikul".DS."customerpicture".
                    DS.$customerId.DS.$signal.DS.$value->getBanner();
                    $newUrl = "";
                    if ($this->fileDriver->isFile($basePath)) {
                        $imageAdapter = $this->imageFactory->create();
                        // Open the image file
                        $imageAdapter->open($basePath);
                        // Get the image width and height
                        $imageWidth = $imageAdapter->getOriginalWidth();
                        $imageHeight = $imageAdapter->getOriginalHeight();
                        
                        $ratio  = $imageWidth/$imageHeight;
                        $height = floor(($width/$ratio)*$mFactor);
                        $width *= $mFactor;
                        $width = floor($width);
                        $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$width."x".$height.DS.
                        "customerpicture".DS.$customerId.DS.$signal.DS.$value->getBanner()."?".$time;
                        $newPath = $this->getBasePath("media").DS."mobikulresized".DS.$width."x".
                        $height.DS."customerpicture".DS.$customerId.DS.$signal.DS.$value->getBanner();
                        $this->helperCatalog->resizeNCache($basePath, $newPath, $width, $height, true);
                    }
                    $returnArray["url"] = $newUrl."?".$time;
                    $returnArray["success"] = true;
                    return $returnArray;
                }
                if ($signal == "profile" && $value->getProfile() != "") {
                    $basePath = $this->getBasePath("media").DS."mobikul".DS."customerpicture".DS.
                    $customerId.DS.$signal.DS.$value->getProfile();
                    $ppHeight = $ppWidth = $this->helper->getValidDimensions($mFactor, 288);
                    if ($this->fileDriver->isFile($basePath)) {
                        $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$ppWidth."x".
                        $ppHeight.DS."customerpicture".DS.$customerId.DS.$signal.DS.$value->getProfile();
                        $newPath = $this->getBasePath("media").DS."mobikulresized".DS.$ppWidth."x".
                        $ppHeight.DS."customerpicture".DS.$customerId.DS.$signal.DS.$value->getProfile();
                        $this->helperCatalog->resizeNCache($basePath, $newPath, $ppWidth, $ppHeight, true);
                    }
                    $returnArray["url"] = $newUrl."?".$time;
                    $returnArray["success"] = true;
                    return $returnArray;
                }
            }
        }
    }
}
