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

abstract class AppCreator extends \Magento\Backend\App\Action
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
     * @var \Webkul\MobikulCore\Model\ResourceModel\Carousel\CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * ResultPageFactory variable
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * CarouselRepository variable
     *
     * @var \Webkul\MobikulCore\Api\CarouselRepositoryInterface
     */
    protected $carouselRepository;
    
    /**
     * FileUploaderFactory variable
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;
    
    /**
     * CarouselDataFactory variable
     *
     * @var \Webkul\MobikulCore\Api\Data\CarouselInterfaceFactory
     */
    protected $carouselDataFactory;
    
    /**
     * ResultForwardFactory variable
     *
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Construct function
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Webkul\MobikulCore\Api\CarouselRepositoryInterface $carouselRepository
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Webkul\MobikulCore\Api\Data\CarouselInterfaceFactory $carouselDataFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Webkul\MobikulCore\Model\ResourceModel\Carousel\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Webkul\MobikulCore\Api\CarouselRepositoryInterface $carouselRepository,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Webkul\MobikulCore\Api\Data\CarouselInterfaceFactory $carouselDataFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Carousel\CollectionFactory $collectionFactory
    ) {
        $this->filter               = $filter;
        $this->coreRegistry         = $coreRegistry;
        $this->storeManager         = $storeManager;
        $this->mediaDirectory       = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->collectionFactory    = $collectionFactory;
        $this->resultPageFactory    = $resultPageFactory;
        $this->resultJsonFactory    = $resultJsonFactory;
        $this->carouselRepository   = $carouselRepository;
        $this->fileUploaderFactory  = $fileUploaderFactory;
        $this->carouselDataFactory  = $carouselDataFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * IsAllowed function
     *
     * @return void
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::appcreator");
    }
}
