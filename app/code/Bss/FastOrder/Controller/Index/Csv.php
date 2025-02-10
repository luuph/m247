<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_FastOrder
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\FastOrder\Controller\Index;

use Bss\FastOrder\Helper\Data;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Class Csv
 * @package Bss\FastOrder\Controller\Index
 */
class Csv extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Bss\FastOrder\Model\Search\Save
     */
    protected $saveModel;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Bss\FastOrder\Helper\ConfigurableProduct
     */
    protected $configurableProductHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected  $storeManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected  $csvHelper;

    /**
     * Csv constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Bss\FastOrder\Model\Search\Save $saveModel
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\FastOrder\Helper\ConfigurableProduct $configurableProductHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param Data $helperData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Bss\FastOrder\Model\Search\Save $saveModel,
        \Psr\Log\LoggerInterface $logger,
        \Bss\FastOrder\Helper\ConfigurableProduct $configurableProductHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Data $helperData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\File\Csv $csvHelper
    ) {
        parent::__construct($context);
        $this->saveModel = $saveModel;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->logger = $logger;
        $this->configurableProductHelper = $configurableProductHelper;
        $this->productRepository = $productRepository;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
        $this->csvHelper = $csvHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = [];
        try {
            // csv function support only simple product not custom option
            $uploader = $this->fileUploaderFactory->create(['fileId' => 'file']);
            $file = $uploader->validateFile();
            if ($this->checkError($file)) {
                return;
            }
            $readCsv = trim(file_get_contents($file['tmp_name']));
            $csvLines = explode("\n", $readCsv);
            $delimiter = $this->_getDelimiter($csvLines[0]);
            $csvFirstLine = explode($delimiter, $csvLines[0]);
            if ($csvFirstLine[0] != 'sku' && $csvFirstLine[1] != 'qty') {
                $this->messageManager->addErrorMessage(
                    __('The file\'s format is not correct. Please download sample csv file and try again.')
                );
                return;
            }
            array_shift($csvLines);
            // foreach row file csv
            $res = $this->getResponseCsv($csvLines);
            $skuNotExist = $res[0];
            $result = $res[1];
            $skuOutStock = $res[2];
            $skuDisable = $res[3];
            $cantAccessProduct = $res[4];
            $rowOutStock = $rowNotExist = $rowDisable = $rowCantAccessProduct = null;

            for ($i = 0; $i < count($csvLines); $i++) {
                if (in_array(explode($delimiter, $csvLines[$i])[0], $skuNotExist)) {
                    $rowNotExist[] = $i + 1;
                } elseif (in_array(explode($delimiter, $csvLines[$i])[0], $cantAccessProduct)) {
                    $rowCantAccessProduct[] = $i + 1;
                } elseif (in_array(explode($delimiter, $csvLines[$i])[0], $skuDisable)) {
                    $rowDisable[] = $i + 1;
                } elseif (in_array(explode($delimiter, $csvLines[$i])[0], $skuOutStock)) {
                    $rowOutStock[] = $i + 1;
                }
            }

            if ($cantAccessProduct) {
                $this->messageManager->addErrorMessage(
                    __(
                        "We can't add product(s) in row %1 to cart because you have no permission to see its.",
                        implode(', ', $rowCantAccessProduct)
                    )
                );
            }

            // mess error sku products not exist
            if ($skuNotExist) {
                $this->messageManager->addErrorMessage(__(
                    'Product(s) in row %1 do not match or do not exist on the site.',
                    implode(', ', $rowNotExist)
                ));
            }
            if (!empty($skuDisable)) {
                if (isset($skuDisable[1])) {
                    $t = __('are disabled');
                } else {
                    $t = __('is disabled');
                }
                $this->messageManager->addErrorMessage(__('Product(s) in row %1 %2.', implode(', ', $rowDisable), $t));
            }
            if (!empty($skuOutStock)) {
                if (isset($skuOutStock[1])) {
                    $t = __('are out of stock');
                } else {
                    $t = __('is out of stock');
                }
                $this->messageManager->addErrorMessage(__('Product(s) in row %1 %2.', implode(', ', $rowOutStock), $t));
            }
            if (count($result) == 0) {
                $this->messageManager->addErrorMessage(__('No Item Imported.'));
            } else {
                $this->messageManager->addSuccessMessage(__('Import Complete.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while reading file.'));
            $this->logger->critical($e);
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }

    /**
     * @param  $csvFirstLine
     * @return mixed|string
     */
    protected function _getDelimiter($csvFirstLine)
    {
        $delimiter = ',';
        $delimiters = [',', '\t', ';', '|', ':'];
        foreach ($delimiters as $value) {
            if (strpos($csvFirstLine, $value) !== false) {
                $delimiter = $value;
                break;
            }
        }
        return $delimiter;
    }

    /**
     * @param null $csvLines
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getResponseCsv($csvLines = null)
    {
        $data = [];
        $skuData = [];
        $qtyData = [];
        $skuExist = [];
        $this->getSkuData($csvLines, $skuData, $qtyData);
        $productCollection = $this->saveModel->getStandardProductCollection($skuData, true);
        $allProductSearch = $this->saveModel->getAllProductSearch($skuData)->getItems();
        $skuImportSuccess = [];
        $disablePr = [];
        $skuOutStock = [];
        $storeId = $this->storeManager->getStore()->getId();

        $cantAccessSku = [];
        if (is_array($allProductSearch)) {
            foreach ($allProductSearch as $product) {
                $skuExist[] = $product["sku"];
            }
        }
        foreach ($productCollection as $product) {
            $productSku = $product->getSku();
            $skuExist[] = $product->getSku();

            $this->helperData->getEventManager()->dispatch(
                'bss_fast_order_prepare_product_add',
                [
                    'product' => $product
                ]
            );
            // Catalog permission checking
            if ($product->getCantAccess()) {
                $cantAccessSku[] = $productSku;
                $skuImportSuccess[] = $productSku;
                $productCollection->removeItemByKey($product->getId());
                continue;
            }

            if ($product->getStatus() == Status::STATUS_DISABLED) {
                $disablePr[] = $productSku;
                $productCollection->removeItemByKey($product->getId());
                continue;
            }
            if (!$product->getIsSalable()) {
                $skuOutStock[] = $productSku;
                $productCollection->removeItemByKey($product->getId());
                continue;
            }
            $skuImportSuccess[] = $productSku;
            if ($parentProductId = $this->configurableProductHelper->getParentProductId($product->getId())) {
                // handle for child of configurable product
                $parentProduct = $this->productRepository->getById($parentProductId);
                $parentProductActiveStores = $parentProduct->getStoreIds();
                $isSalable = true;
                if ($parentProduct->getStatus() == Status::STATUS_DISABLED) {
                    $disablePr[] = $productSku;
                    $productCollection->removeItemByKey($product->getId());
                    $isSalable = false;
                    continue;
                }
                if (!$parentProduct->getIsSalable()) {
                    $skuOutStock[] = $productSku;
                    $productCollection->removeItemByKey($product->getId());
                    $isSalable = false;
                    continue;
                }
                if (in_array($storeId, $parentProductActiveStores) && $isSalable) {
                    $childData = $this->configurableProductHelper->getChildProductData(
                        $parentProduct,
                        $product
                    );
                    if (!empty($childData)) {
                        $product->setData($childData->getData());
                    }
                }
            }
            $qty = 1;
            if (!empty($qtyData[$productSku])) {
                $qty = $qtyData[$productSku];
            }
            $product->setQty($qty);
        }
        $skuNotExist = array_diff($skuData, $skuExist);
        if (!empty($skuImportSuccess)) {
            $data = $productCollection->toArray([
                'name',
                'sku',
                'entity_id',
                'type_id',
                'product_hide_price',
                'product_hide_html',
                'product_thumbnail',
                'product_url',
                'popup',
                'product_price',
                'product_price_amount',
                'product_price_exc_tax_html',
                'product_price_exc_tax',
                'qty',
                'popup_html',
                'configurable_attributes',
                'child_product_id'
            ]);
        }

        return [$skuNotExist, $data, $skuOutStock, $disablePr, $cantAccessSku];
    }

    /**
     * @param $csvLines
     * @param $skuData
     * @param $qtyData
     */
    private function getSkuData($csvLines, &$skuData, &$qtyData)
    {
        $delimiter = $this->_getDelimiter($csvLines[0]);
        foreach ($csvLines as $csvLine) {
            $arrLine = explode($delimiter, $csvLine);
            $sku = ltrim(rtrim($arrLine[0]));
            $qty = $arrLine[1];

            if (!$sku) {
                continue;
            }
            $skuData[] = $sku;

            if (empty($qty)) {
                $qty = 1;
            }
            $qtyData[$sku] = $qty;
        }
    }

    /**
     * @param  null $file
     * @return bool
     */
    protected function checkError($file = null)
    {
        if (!is_array($file) || empty($file)) {
            $this->messageManager->addErrorMessage(__('We can\'t import item to your table right now.'));
            return true;
        }

        if ($file['error'] > 0) {
            $this->messageManager->addErrorMessage(__('We can\'t import item to your table right now.'));
            return true;
        }
        if (pathinfo($file['name'], PATHINFO_EXTENSION) != 'csv') {
            $this->messageManager->addErrorMessage(
                __('The file\'s format is not correct. Please download sample csv file and try again.')
            );
            return true;
        }
        return false;
    }
}
