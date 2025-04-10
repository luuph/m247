<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphQl
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Catalog;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * CompareList resolver
 */
class CompareList extends AbstractCatalog implements ResolverInterface
{
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
            $currency = $this->wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
            $environment = $this->emulate->startEnvironmentEmulation(
                $this->storeId,
                \Magento\Framework\App\Area::AREA_FRONTEND,
                true
            );
            // Setting currency /////////////////////////////////////////////////////
            $this->store->setCurrentCurrencyCode($currency);
            // Checking is swatch allowed on colletion page /////////////////////////
            $this->returnArray["showSwatchOnCollection"] = (bool)$this->helper->getConfigData(
                "catalog/frontend/show_swatches_in_product_list"
            );
            // Getting compare list data ////////////////////////////////////////////
            if ($this->items === null) {
                $this->compare->setAllowUsedFlat(false);
                $this->items = $this->compareItemCollectionFactory->create();
                $this->items->useProductItem(true)->setStoreId($this->storeId);
                if ($this->customerId != 0) {
                    $this->items->setCustomerId($this->customerId);
                } else {
                    $this->items->setVisitorId($this->customerVisitor->getId());
                }
                $attributes = $this->catalogConfig->getProductAttributes();
                $this->items
                    ->addAttributeToSelect($attributes)
                    ->loadComparableAttributes()
                    ->setVisibility($this->productVisibility->getVisibleInSiteIds());
                
                $this->eventManager->dispatch(
                    'mobikul_product_collection_filter_event',
                    ['collection' => $this->items]
                );
            }
            // Getting product list /////////////////////////////////////////////////
            $productList = [];
            $this->items->addMinimalPrice();
            foreach ($this->items as $eachProduct) {
                $product = $this->helperCatalog->getOneProductRelevantData(
                    $eachProduct,
                    $this->storeId,
                    $this->width,
                    $this->customerId
                );
                $productList[] = $product;
            }
            $this->returnArray["productList"] = $productList;
            // Getting attribute value list /////////////////////////////////////////
            $block = $this->compareListBlock;
            $attributeValueList = [];
            foreach ($this->items->getComparableAttributes() as $attribute) {
                $eachRow = [];
                $eachRow["attributeName"] = $this->escaper->escapeHtml(
                    $attribute->getStoreLabel($this->storeId) ? $attribute->getStoreLabel($this->storeId) : __(
                        $attribute->getFrontendLabel()
                    )
                );
                foreach ($this->items as $item) {
                    $eachItem = "";
                    switch ($attribute->getAttributeCode()) {
                        case "price":
                            $eachItem = $this->helperCatalog->stripTags(
                                $this->pricingHelper->currency($item->getFinalPrice())
                            );
                            break;
                        case "small_image":
                            $eachItem = $block->getImage($item, "product_small_image")->toHtml();
                            break;
                        default:
                            $attributeHtml = (string) $block->getProductAttributeValue(
                                $item,
                                $attribute
                            );
                            $value = (is_string($attributeHtml)) ? $attributeHtml : "";
                            $eachItem = $this->catalogHelperOutput->productAttribute(
                                $item,
                                $value,
                                $attribute->getAttributeCode()
                            );
                            break;

                    }
                    $eachRow["value"][] = $eachItem;
                }
                $attributeValueList[] = $eachRow;
            }
            $this->returnArray["attributeValueList"] = $attributeValueList;
            $this->returnArray["success"] = true;
            $this->customerSession->setCustomerId(null);
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * Function verify Request to authenticate the request
     *
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->mFactor = $this->wholeData["mFactor"] ?? 1;
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            } elseif ($this->customerId != 0) {
                $this->customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
