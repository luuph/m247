<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @Copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Block;

class Barcode extends \Magento\Backend\Block\Template
{
    /**
     * Filter variable
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $_filter;
    
    /**
     * IoFile variable
     *
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $_ioFile;
    
    /**
     * StoreManager variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;
    
    /**
     * DirectoryList variable
     *
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    public $_directoryList;
    
    /**
     * HelperBarcode variable
     *
     * @var \Webkul\MobikulCore\Helper\Barcode
     */
    public $_helperBarcode;
    
    /**
     * CollectionFactory variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $_collectionFactory;
    
    /**
     * Constructor function
     *
     * @param \Webkul\MobikulCore\Helper\Barcode $helperBarcode
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Barcode $helperBarcode,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->_ioFile = $ioFile;
        $this->_storeManager  = $storeManager;
        $this->_directoryList = $directoryList;
        $this->_helperBarcode = $helperBarcode;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * GetProductCollection function
     *
     * @return void
     */
    protected function getProductCollection()
    {
        return $this->_filter->getCollection(
            $this->_collectionFactory->create()
        );
    }

    /**
     * BaseUrl function
     *
     * @return void
     */
    protected function baseUrl()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * MediaPath function
     *
     * @return void
     */
    protected function mediaPath()
    {
        return $this->_directoryList->getPath("media");
    }

    /**
     * CreateDirectory function
     *
     * @param string $path
     * @return void
     */
    protected function createDirectory($path)
    {
        $this->_ioFile->mkdir($path, 0777);
    }

    /**
     * GenerateBarcode function
     *
     * @param string $path
     * @param mixed $productData
     * @return void
     */
    protected function generateBarcode($path, $productData)
    {
        $this->_helperBarcode->generatebarcode(
            $path,
            $productData,
            20,
            "horizontal",
            "code128",
            false,
            1
        );
    }
}
