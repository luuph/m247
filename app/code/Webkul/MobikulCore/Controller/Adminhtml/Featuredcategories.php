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
 * Featuredcategories Class controller
 */
abstract class Featuredcategories extends \Magento\Backend\App\Action
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
     * JsonHelper variable
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    
    /**
     * StoreManager variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * MediaDirectory variable
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $mediaDirectory;
    
    /**
     * ResultPageFactory variable
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * CollectionFactory variable
     *
     * @var \Webkul\MobikulCore\Model\ResourceModel\Featuredcategories\CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * ResultJsonFactory variable
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * CategoryRepository variable
     *
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;
    
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
     * Undocumented variable
     *
     * @var [type]
     */
    protected $categoryResourceModel;
    
    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $featuredcategoriesRepository;

    /**
     * FeaturedcategoriesDataFactory variable
     *
     * @var Webkul\MobikulCore\Api\Data\FeaturedcategoriesInterfaceFactory
     */
    protected $featuredcategoriesDataFactory;

    /**
     * FormKeyValidator variable
     *
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

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
     * Constructor function
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Webkul\MobikulCore\Api\FeaturedcategoriesRepositoryInterface $featuredcategoriesRepository
     * @param \Webkul\MobikulCore\Api\Data\FeaturedcategoriesInterfaceFactory $featuredcategoriesDataFactory
     * @param \Webkul\MobikulCore\Model\ResourceModel\Featuredcategories\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Downloadable\Helper\File $fileHelper
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Webkul\MobikulCore\Api\FeaturedcategoriesRepositoryInterface $featuredcategoriesRepository,
        \Webkul\MobikulCore\Api\Data\FeaturedcategoriesInterfaceFactory $featuredcategoriesDataFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Featuredcategories\CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Downloadable\Helper\File $fileHelper
    ) {
        parent::__construct($context);
        $this->date = $date;
        $this->filter = $filter;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->formKeyValidator = $formKeyValidator;
        $this->collectionFactory = $collectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->categoryRepository = $categoryRepository;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->featuredcategoriesRepository = $featuredcategoriesRepository;
        $this->featuredcategoriesDataFactory = $featuredcategoriesDataFactory;
        $this->fileDriver = $fileDriver;
        $this->fileHelper = $fileHelper;
    }

    /**
     * _isAllowed function
     *
     * @return Boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::featuredcategories");
    }
}
