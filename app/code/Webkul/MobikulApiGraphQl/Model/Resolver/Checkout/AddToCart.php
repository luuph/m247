<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphql
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Checkout;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * AddToCart resolver
 */
class AddToCart extends AbstractCheckout implements ResolverInterface
{
    /**
     * Params variable
     *
     * @var array
     */
    protected $params;

    /**
     * @var \Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite
     */
    protected $getStockIdForCurrentWebsite = null;

    /**
     * @var \Magento\InventorySalesApi\Model\GetStockItemDataInterface
     */
    protected $getStockItemDataInterface = null;
    
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->wholeData = $args;
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            if ($this->helper->getConfigData("sales/minimum_order/active")) {
                $this->returnArray["minimumAmount"] = (int)$this->helper->getConfigData("sales/minimum_order/amount");
                $this->returnArray["minimumFormattedAmount"] = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($this->returnArray["minimumAmount"])
                );
            } else {
                $this->returnArray["minimumAmount"] = 0;
                $this->returnArray["minimumFormattedAmount"] = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice(0)
                );
            }
            $quote = new \Magento\Framework\DataObject();
            // added check for expired quote ////////////////////////////////////
            if ($this->quoteId) {
                $size = $this->quoteFactory
                    ->create()
                    ->getCollection()
                    ->addFieldToFilter("entity_id", ["eq" => $this->quoteId])
                    ->getSize();
                if (!$size) {
                    $this->quoteId = 0;
                }
            }
            // end added check for expired quote ////////////////////////////////
            if ($this->customerId == 0 && $this->quoteId == 0) {
                $this->setQuoteIdData();
            }
            if ($this->qty == 0) {
                $this->qty = 1;
            }
            if ($this->customerId != 0) {
                $this->saveQuoteCustomerData();
            }
            $quote = $this->helper->getQuoteById($this->quoteId)->setStoreId($this->storeId);
            $product = $this->productFactory->create()->setStoreId($this->storeId)->load($this->productId);
            if ($this->qty && !($product->getTypeId() == "downloadable")) {
                $this->checkStockData($product);
            }

            $request = [];
            $paramOption = [];
            $filesToDelete = [];

            $request = $this->setProductParamOptions($product);

            $request["qty"] = $this->qty;
            $request = $this->_getProductRequest($request);
            $productAdded = $quote->addProduct($product, $request);
            $result = $this->checkRelatedProduct($quote);
            $allAdded = $result['allAdded'];
            $allAvailable = $result['allAvailable'];
            $quote->collectTotals()->save();
            if (!$productAdded || is_string($productAdded)) {
                $this->returnArray["message"] = __("Unable to add product to cart.");
                if (is_string($productAdded)) {
                    $this->returnArray["message"] = $productAdded;
                }
                return $this->returnArray;
            } else {
                $this->returnArray["cartCount"] = (int) $this->helper->getCartCount($quote);
            }
            $this->returnArray["message"] =
                __("You added %1 to your shopping cart.", $this->helperCatalog->stripTags($product->getName()));
            if (!$allAvailable) {
                $this->returnArray["message"] .= __(" but, We don't have some of the products you want.");
            }
            if (!$allAdded) {
                $this->returnArray["message"] .= __(" but, We don't have as many of some products as you want.");
            }
            // delete files uploaded for custom option /////////////////
            foreach ($filesToDelete as $eachFile) {
                $this->fileDriver->deleteFile($eachFile);
            }
            // validate minimum amount check ////////////////////////////////////
            $isCheckoutAllowed = $quote->validateMinimumAmount();
            if (!$isCheckoutAllowed) {
                $this->returnArray["isCheckoutAllowed"] = false;
                $this->returnArray["descriptionMessage"] = $this->helper->getConfigData(
                    "sales/minimum_order/description"
                );
            } elseif ($quote->getHasError()) {
                $this->returnArray["isCheckoutAllowed"] = false;
            } else {
                $this->returnArray["isCheckoutAllowed"] = true;
            }
            $this->returnArray["canGuestCheckoutDownloadable"] = false;
            if ($this->helper->getConfigData("catalog/downloadable/disable_guest_checkout") == 0) {
                $this->returnArray["canGuestCheckoutDownloadable"] = true;
            }
            if ($this->checkoutHelper->isAllowedGuestCheckout($quote)) {
                $this->returnArray["isAllowedGuestCheckout"] = $this->checkoutHelper->isAllowedGuestCheckout(
                    $quote
                );
            }
            $this->returnArray["cartTotal"] = $quote->getGrandTotal();
            $this->returnArray["cartTotalFormattedAmount"] = $this->helperCatalog->stripTags(
                $this->checkoutHelper->formatPrice($quote->getGrandTotal())
            );
            $this->returnArray["isVirtual"] = (bool)$quote->getIsVirtual();
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * CheckRelatedProduct function
     *
     * @param mixed $quote
     * @return void
     */
    protected function checkRelatedProduct($quote)
    {
        $allAdded = true;
        $allAvailable = true;
        if (!empty($this->relatedProducts)) {
            foreach ($this->relatedProducts as $productId) {
                $productId = (int)$productId;
                if (!$productId) {
                    continue;
                }
                $relatedProduct = $this->productFactory->create()->setStoreId($this->storeId)->load($productId);
                if ($relatedProduct->getId() && $relatedProduct->isVisibleInCatalog()) {
                    try {
                        $quote->addProduct($relatedProduct);
                    } catch (\Exception $e) {
                        $allAdded = false;
                    }
                } else {
                    $allAvailable = false;
                }
            }
        }
        return ['allAdded'=>$allAdded, 'allAvailable'=>$allAvailable];
    }

    /**
     * SetProductParamOptions function
     *
     * @param mixed $product
     * @return void
     */
    protected function setProductParamOptions($product)
    {
        $request = [];
        $paramOption = [];
        if (isset($this->params["options"])) {
            $productOptions = $this->params["options"];
            foreach ($productOptions as $optionArr) {
                $optionId = $optionArr['id'];
                $values = $optionArr['value'];
                $option = $this->productOption->load($optionId);
                $optionType = $option->getType();
                if (in_array($optionType, ["multiple", "checkbox"])) {
                    foreach ($values as $optionValue) {
                        $paramOption[$optionId][] = $optionValue;
                    }
                } elseif (in_array($optionType, ["radio", "drop_down", "area", "field"])) {
                    $paramOption[$optionId] = $values[0];
                } elseif ($optionType == "file") {
                    // downloading file /////////////////////////////////////////////
                    $base64String = $productOptions[$optionId]["encodeImage"];
                    $fileName = time().$productOptions[$optionId]["name"];
                    $fileType = $productOptions[$optionId]["type"];
                    $fileWithPath = $this->helperCatalog->getBasePath().DS.$fileName;
                    $ifp = $this->fileDriver->fileOpen($fileWithPath, "wb");
                    $this->fileDriver->fileWrite($ifp, $this->base64Json->unserialize($base64String));
                    // assigning file to option /////////////////////////////////////
                    $fileOption = [
                        "type" => $fileType,
                        "title" => $fileName,
                        "quote_path" => DS."media".DS.$fileName,
                        "fullpath" => $fileWithPath,
                        "secret_key" => substr(hash("sha256", $this->fileDriver->fileGetContents($fileWithPath)), 0, 20)
                    ];
                    $filesToDelete[] = $fileWithPath;
                    $paramOption[$optionId] = $fileOption;
                } elseif ($optionType == "date") {
                    $paramOption[$optionId]["day"] = $values["day"];
                    $paramOption[$optionId]["year"] = $values["year"];
                    $paramOption[$optionId]["month"] = $values["month"];
                    if ($this->helperCatalog->useCalenderForCustomOption()) {
                        $paramOption[$optionId]["date"] = $this->getCustomOptionDate($values);
                    }
                } elseif ($optionType == "date_time") {
                    $paramOption[$optionId]["day"] = $values["day"];
                    $paramOption[$optionId]["year"] = $values["year"];
                    $paramOption[$optionId]["hour"] = $values["hour"];
                    $paramOption[$optionId]["month"] = $values["month"];
                    $paramOption[$optionId]["minute"] = $values["minute"];
                    $paramOption[$optionId]["dayPart"] = $values["day_part"];
                    if ($this->helperCatalog->useCalenderForCustomOption()) {
                        $paramOption[$optionId]["date"] = $this->getCustomOptionDate($values);
                    }
                } elseif ($optionType == "time") {
                    $paramOption[$optionId]["hour"] = $values["hour"];
                    $paramOption[$optionId]["minute"] = $values["minute"];
                    $paramOption[$optionId]["dayPart"] = $values["day_part"];
                }
            }
        }
        if ($product->getTypeId() == "downloadable") {
            if (isset($this->params["links"])) {
                $request = ["links"=>$this->params["links"], "options"=>$paramOption, "product"=>$this->productId];
            } else {
                $request = ["options"=>$paramOption, "product"=>$this->productId];
            }
        } elseif ($product->getTypeId() == "grouped") {
            if (isset($this->params["superGroup"])) {
                $request = [
                    "super_group"=>array_column($this->params["superGroup"], 'value', 'id'),
                    "product"=>$this->productId
                ];
            }
        } elseif ($product->getTypeId() == "configurable") {
            if (isset($this->params["superAttribute"])) {
                $request = [
                    "super_attribute"=>array_column($this->params["superAttribute"], 'value', 'id'),
                    "options"=>$paramOption,
                    "product"=>$this->productId
                ];
            }
        } elseif ($product->getTypeId() == "hotelbooking") {
            if (isset($this->params["super_attribute"])) {
                $request = [
                    "super_attribute"=>$this->params["super_attribute"],
                    "options"=>$paramOption,
                    "product"=>$this->productId
                ];
            }
        } elseif ($product->getTypeId() == "bundle") {
            if (isset($this->params["bundleOption"])) {
                $this->getBundleOption($product);
                $request = [
                    "bundle_option"=>array_column($this->params["bundleOption"], 'value', 'id'),
                    "bundle_option_qty"=>array_column($this->params["bundleOption"], 'qty', 'id'),
                    "options"=>$paramOption,
                    "product"=>$this->productId
                ];
            }
        } else {
            $request = ["options"=>$paramOption, "product"=>$this->productId];
        }
        return $request;
    }

    /**
     * GetBundleOption function
     *
     * @param mixed $product
     * @return void
     */
    public function getBundleOption($product)
    {
        $this->coreRegistry->register("product", $product);
        $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
            $product->getTypeInstance(true)->getOptionsIds($product),
            $product
        );
        foreach ($selectionCollection as $option) {
            $selectedOptions = $this->params["bundle_option"][$option->getOptionId()] ?? 0;
            if (!empty($selectedOptions) &&
                (
                    $option->getSelectionId() == $selectedOptions ||
                    (is_array($selectedOptions) && in_array($option->getSelectionId(), $selectedOptions))
                )
            ) {
                $selectionQty = $option->getSelectionQty() * 1;
                $key = $option->getOptionId();
                if (isset($this->params["bundle_option_qty"][$key])) {
                    $probablyRequestedQty = $this->params["bundle_option_qty"][$key];
                }
                if ($selectionQty > 1) {
                    $requestedQty = $selectionQty * $this->qty;
                } elseif (isset($probablyRequestedQty)) {
                    $requestedQty = $probablyRequestedQty * $this->qty;
                } else {
                    $requestedQty = 1;
                }
                $associateBundleProduct = $this->productFactory->create()->load($option->getProductId());
                $availableQty = $this->stockRegistry->getStockItem($associateBundleProduct->getId())->getQty();
                if ($associateBundleProduct->getIsSalable()) {
                    if ($requestedQty > $availableQty) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("The requested quantity of %1 is not available", $option->getName())
                        );
                    }
                }
            }
        }
    }

    /**
     * Get Custom Option Date
     *
     * @param array $values
     *
     * @return string
     */
    public function getCustomOptionDate($values)
    {
        $date = [];
        $dateOrder = explode(",", $this->helper->getConfigData("catalog/custom_options/date_fields_order"));
        foreach ($dateOrder as $order) {
            if ($order == "m") {
                $date[] = $values["month"];
            } elseif ($order == "d") {
                $date[] = $values["day"];
            } else {
                $date[] = $values["year"];
            }
        }
        return implode("/", $date);
    }

    /**
     * CheckStockData function
     *
     * @param mixed $product
     * @return void
     */
    protected function checkStockData($product)
    {
        $stockData = $this->stockRegistry->getStockItem($product->getId());
        $availableQty = $stockData->getQty();
        $manageStock = $stockData->getManageStock();

        if ($this->getStockIdForCurrentWebsite()) {
            $stockId = $this->getStockIdForCurrentWebsite()->execute();
            $stockItemData = $this->getStockItemDataInterface()->execute($product->getSku(), $stockId);
            $availableQty = $stockItemData[
                \Magento\InventorySalesApi\Model\GetStockItemDataInterface::QUANTITY
            ] ?? $availableQty;
        }

        if ($this->qty <= $availableQty) {
            $filter = new \Magento\Framework\Filter\LocalizedToNormalized(
                ["locale"=>$this->localeResolver->getLocale()]
            );
            $this->qty = $filter->filter((String)$this->qty);
        } else {
            if (!in_array($product->getTypeId(), ["grouped", "configurable", "bundle"]) && $manageStock) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The requested quantity is not available")
                );
            }
        }
    }

    protected function getStockIdForCurrentWebsite() {
        if ($this->getStockIdForCurrentWebsite == null) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->getStockIdForCurrentWebsite = $objectManager->create(
                \Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite::class
            );
        }
        return $this->getStockIdForCurrentWebsite;
    }

    protected function getStockItemDataInterface() {
        if ($this->getStockItemDataInterface == null) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->getStockItemDataInterface = $objectManager->create(
                \Magento\InventorySalesApi\Model\GetStockItemDataInterface::class
            );
        }
        return $this->getStockItemDataInterface;
    }
    
    /**
     * SetQuoteIdData function
     *
     * @return void
     */
    protected function setQuoteIdData()
    {
        $quote = $this->quoteFactory->create()
            ->setStoreId($this->storeId)
            ->setIsActive(true)
            ->setIsMultiShipping(false)
            ->save();
        $quote->getBillingAddress();
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->collectTotals()->save();
        $this->quoteId = (int) $quote->getId();
        $this->returnArray["quoteId"] = $this->quoteId;
    }

    /**
     * SaveQuoteCustomerData function
     *
     * @return void
     */
    protected function saveQuoteCustomerData()
    {
        $quote = $this->helper->getCustomerQuote($this->customerId);
        $this->quoteId = $quote->getId();
        if ($quote->getId() < 0 || !$this->quoteId) {
            $quote = $this->quoteFactory->create()
                ->setStoreId($this->storeId)
                ->setIsActive(true)
                ->setIsMultiShipping(false)
                ->save();
            $this->quoteId = (int) $quote->getId();
            $customer = $this->customerRepository->getById($this->customerId);
            $quote->assignCustomer($customer);
            $quote->setCustomer($customer);
            $quote->getBillingAddress();
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->collectTotals()->save();
        }
        if ($quote->getIsVirtual()) {
            $returnArray["isVirtual"] = (bool)$quote->getIsVirtual();
        }
    }

    /**
     * Function to verify request
     *
     * @return void|json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->qty = $this->wholeData["qty"] ?? 1;
            $this->params = $this->wholeData["params"] ?? [];
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->relatedProducts = $this->wholeData["relatedProducts"] ?? [];
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Function to get Product request
     *
     * @param object $requestInfo requestInfo
     *
     * @return object
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof \Magento\Framework\DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new \Magento\Framework\DataObject(["qty"=>$requestInfo]);
        } elseif (is_array($requestInfo)) {
            $request = new \Magento\Framework\DataObject($requestInfo);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("We found an invalid request for adding product to quote.")
            );
        }
        $this->getRequestInfoFilter()->filter($request);
        return $request;
    }

    /**
     * Function getRequestInfoFilter
     *
     * @return object filter
     */
    protected function getRequestInfoFilter()
    {
        if ($this->requestInfoFilter === null) {
            $this->requestInfoFilter = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Checkout\Model\Cart\RequestInfoFilterInterface::class
            );
        }
        return $this->requestInfoFilter;
    }

    /**
     * Function to get item Data
     *
     * @param object $quote   quote
     * @param object $product project
     *
     * @return bool
     */
    public function getItemByProduct($quote, $product)
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->representProduct($product)) {
                return $item;
            }
        }
        return false;
    }
}
