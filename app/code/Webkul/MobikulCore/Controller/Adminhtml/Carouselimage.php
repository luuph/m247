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

namespace Webkul\MobikulCore\Controller\Adminhtml;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Carouselimage Class controller
 */
abstract class Carouselimage extends \Magento\Backend\App\Action
{
   /**
    * Filter variable
    *
    * @var \Magento\Ui\Component\MassAction\Filter
    */
    protected $filter;

    /**
     * StoreManager variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CoreRegistry variable
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * MediaDirectory variable
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $mediaDirectory;
    
    /**
     * ResultJsonFactory variable
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * CollectionFactory variable
     *
     * @var \Webkul\MobikulCore\Model\ResourceModel\Carouselimage\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * ResultPageFactory variable
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * FileUploaderFactory variable
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * ResultForwardFactory variable
     *
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * CarouselimageRepository variable
     *
     * @var \Webkul\MobikulCore\Api\CarouselimageRepositoryInterface
     */
    protected $carouselimageRepository;

    /**
     * CarouselimageDataFactory variable
     *
     * @var \Webkul\MobikulCore\Api\Data\CarouselimageInterfaceFactory
     */
    protected $carouselimageDataFactory;

    /**
     * ProductRepositoryInterface variable
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepositoryInterface;

    /**
     * CategoryRepositoryInterface variable
     *
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepositoryInterface;

    /**
     * FileDriver variable
     *
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * FileHelper variable
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $fileHelper;

    /**
     * Undocumented function
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface
     * @param \Webkul\MobikulCore\Api\CarouselimageRepositoryInterface $carouselimageRepository
     * @param \Webkul\MobikulCore\Api\Data\CarouselimageInterfaceFactory $carouselimageDataFactory
     * @param \Webkul\MobikulCore\Model\ResourceModel\Carouselimage\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Downloadable\Helper\File $fileHelper
     */

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface,
        \Webkul\MobikulCore\Api\CarouselimageRepositoryInterface $carouselimageRepository,
        \Webkul\MobikulCore\Api\Data\CarouselimageInterfaceFactory $carouselimageDataFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Carouselimage\CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Downloadable\Helper\File $fileHelper
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->collectionFactory = $collectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->carouselimageRepository = $carouselimageRepository;
        $this->carouselimageDataFactory = $carouselimageDataFactory;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fileDriver = $fileDriver;
        $this->fileHelper = $fileHelper;
    }

    /**
     * IsAllowed function
     *
     * @return void
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::carouselimage");
    }
}
