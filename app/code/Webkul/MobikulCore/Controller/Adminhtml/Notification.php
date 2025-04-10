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
 * Notification Class controller
 */
abstract class Notification extends \Magento\Backend\App\Action
{
    /**
     * Date variable
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * Filter variable
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;
    
    /**
     * Helper variable
     *
     * @var \Webkul\MobikulCore\Helper\Data
     */
    protected $helper;
    
    /**
     * JsonHelper variable
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    
    /**
     * EntityType variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Flat
     */
    protected $entityType;
    
    /**
     * ProductType variable
     *
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $productType;
    
    /**
     * StoreManager variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * AttributeSet variable
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    protected $attributeSet;
    
    /**
     * MediaDirectory variable
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $mediaDirectory;
    
    /**
     * EntityAttribute variable
     *
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $entityAttribute;
    
    /**
     * ProductResource variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;
    
    /**
     * CategoryResource variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $categoryResource;
    
    /**
     * ResultJsonFactory variable
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * ResultPageFactory variable
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * CollectionFactory variable
     *
     * @var \Webkul\MobikulCore\Model\ResourceModel\Notification\CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * CoreRegistry variable
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    
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
     * NotificationRepository variable
     *
     * @var \Webkul\MobikulCore\Api\NotificationRepositoryInterface
     */
    protected $notificationRepository;
    
    /**
     * NotificationDataFactory variable
     *
     * @var \Webkul\MobikulCore\Api\Data\NotificationInterfaceFactory
     */
    protected $notificationDataFactory;
    
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
     * Serializer variable
     *
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serializer;
    
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
     * Curl variable
     *
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;
    
    /**
     * FilesystemIo variable
     *
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $filesystemIo;
    
    /**
     * RemoteAddress variable
     *
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    /**
     * Construct function
     *
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Eav\Model\Entity\Attribute $entityAttribute
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\Flat $entityType
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResource
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attributeSet
     * @param \Webkul\MobikulCore\Api\NotificationRepositoryInterface $notificationRepository
     * @param \Webkul\MobikulCore\Api\Data\NotificationInterfaceFactory $notificationDataFactory
     * @param \Webkul\MobikulCore\Model\ResourceModel\Notification\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serializer
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Downloadable\Helper\File $fileHelper
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Filesystem\Io\File $filesystemIo
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\Flat $entityType,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attributeSet,
        \Webkul\MobikulCore\Api\NotificationRepositoryInterface $notificationRepository,
        \Webkul\MobikulCore\Api\Data\NotificationInterfaceFactory $notificationDataFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Notification\CollectionFactory $collectionFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Downloadable\Helper\File $fileHelper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Filesystem\Io\File $filesystemIo,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
    ) {
        parent::__construct($context);
        $this->date = $date;
        $this->filter = $filter;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        $this->entityType = $entityType;
        $this->productType = $productType;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->attributeSet = $attributeSet;
        $this->entityAttribute = $entityAttribute;
        $this->productResource = $productResource;
        $this->categoryResource = $categoryResource;
        $this->collectionFactory = $collectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->notificationRepository = $notificationRepository;
        $this->notificationDataFactory = $notificationDataFactory;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->serializer = $serializer;
        $this->fileDriver = $fileDriver;
        $this->fileHelper = $fileHelper;
        $this->curl = $curl;
        $this->filesystemIo = $filesystemIo;
        $this->remoteAddress = $remoteAddress;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * IsAllowed function
     *
     * @return void
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::notification");
    }
}
