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

namespace Webkul\MobikulCore\Ui\Component\Listing\Columns;

/**
 * BarCodeImage class provides barcode data to product grid
 */
class BarCodeImage extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * BaseDir variable
     *
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    private $baseDir;
    
    /**
     * StoreManager variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * HelperBarcode variable
     *
     * @var \Webkul\MobikulCore\Helper\Barcode
     */
    private $helperBarcode;
    
    /**
     * IoFile variable
     *
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;
    
    /**
     * Escaper variable
     *
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * Contructor function for helper classes
     *
     * @param \Webkul\MobikulCore\Helper\Barcode $helperBarcode
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     * @param array $components
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Barcode $helperBarcode,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Escaper $escaper,
        array $data = [],
        array $components = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->baseDir = $dir->getPath("media");
        $this->storeManager = $storeManager;
        $this->helperBarcode = $helperBarcode;
        $this->ioFile = $ioFile;
        $this->fileDriver = $fileDriver;
        $this->escaper=$escaper;
    }

    /**
     * PrepareDataSource function
     *
     * @param array $dataSource
     * @return array $dataSource
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            $target    = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $fieldName = $this->getData("name");
            foreach ($dataSource["data"]["items"] as $key => $item) {
                $basePath = $this->baseDir."/"."barcode"."/"."product"."/".$item["entity_id"]."/";
                $remove = ['039;','&','#', 'amp', ';','"'];
                $name = str_replace($remove, "", $this->escaper->escapeHtml($item["sku"]));
                $fileName = str_replace(" ", "_", $name).".png";
                if (!$this->fileDriver->isFile($basePath.$fileName)) {
                    $this->ioFile->mkdir($basePath, 0777);
                    $path = $basePath.$fileName;
                    $this->helperBarcode->generatebarcode($path, $item["sku"], 20, "horizontal", "code128", false, 1);
                }
                $srcUrl = $target."barcode"."/"."product"."/".$item["entity_id"]."/".$fileName;
                $dataSource["data"]["items"][$key][$fieldName."_src"] = $srcUrl;
                $dataSource["data"]["items"][$key][$fieldName."_alt"] = $item["sku"];
            }
        }
        return $dataSource;
    }
}
