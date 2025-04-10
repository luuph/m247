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

namespace Webkul\MobikulCore\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * ProductSaveAfterOvserver Class observer
 */
class ProductSaveAfterObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Request variable
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * ProductRepository variable
     *
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * ObjectManager variable
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * StoreManager variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ProductAction variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    protected $productAction;
    
    /**
     * File variable
     *
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * Filesystem variable
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * FileUploaderFactory variable
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * FileUploaderFactory variable
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    
    /**
     * Construct function
     *
     * @param RequestInterface $request
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Action $productAction
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        RequestInterface $request,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->file = $file;
        $this->request = $request;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
        $this->productAction = $productAction;
        $this->fileUploaderFactory = $uploaderFactory;
        $this->productRepository = $productRepository;
        $this->_eventManager = $eventManager;
    }

    /**
     * Execute function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $productData = $this->request->getParam("product");
        $files = $this->request->getFiles();
        $deleteArray = $this->request->getParam("texture_image_delete");
        $storeId = $this->request->getParam("store");
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            "catalog/product/ar/textureimage/"
        ).$product->getId();
        $textureImages = json_decode($product->getArTextureImage() ?? "");
        if (!empty($deleteArray)) {
            $imageForDelete = [];
            $finalTextureImages = [];
            foreach ($deleteArray as $url => $delete) {
                if ($delete) {
                    $imageForDelete = $this->deleteTextureImage($textureImages, $url, $path, $imageForDelete);
                }
            }
            $finalTextureImages = array_diff((array)$textureImages, $imageForDelete);
        } else {
            $finalTextureImages = json_decode($product->getArTextureImage() ?? "");
        }
        $finalTextureImages = $this->getTextureImage($finalTextureImages, $files, $path);
        $this->_eventManager->dispatch(
            'invalidate_mobikul_catalog_cache'
        );
    }

    /**
     * DeleteTextureImage function
     *
     * @param mixed $textureImages
     * @param mixed $url
     * @param mixed $path
     * @param mixed $imageForDelete
     * @return void
     */
    public function deleteTextureImage($textureImages, $url, $path, $imageForDelete)
    {
        foreach ($textureImages as $image) {
            if (strpos($image, $url) !== false) {
                $imageForDelete[] = $image;
                $exploadedFileArr = explode("/", $image);
                $fileName = $exploadedFileArr[count($exploadedFileArr) - 1];
                if ($this->file->isExists($path."/".$fileName)) {
                    $this->file->deleteFile($path."/".$fileName);
                }
            }
        }
        return $imageForDelete;
    }

    /**
     * GetTextureImage function
     *
     * @param mixed $finalTextureImages
     * @param mixed $files
     * @param mixed $path
     * @return void
     */
    public function getTextureImage($finalTextureImages, $files, $path)
    {
        if (!empty($files["ar_texture_image"])) {
            $keys = array_keys($files["ar_texture_image"]);
            foreach ($keys as $value) {
                try {
                    /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                    $uploader = $this->fileUploaderFactory->create(["fileId"=>"ar_texture_image[".$value."]"]);
                    $uploader->setAllowedExtensions(["png", "jpg", "jpeg"]);
                    $uploader->setAllowRenameFiles(true);
                    $result = $uploader->save($path);
                    $url = $this->getMediaUrl()."catalog/product/ar/textureimage/".
                        $product->getId()."/".$result["file"];
                    $finalTextureImages[] = $url;
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        return $finalTextureImages;
    }

    /**
     * GetMediaUrl function
     *
     * @return void
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
