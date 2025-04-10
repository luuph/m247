<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Customer;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Wishlist resolver
 */
class Wishlist extends AbstractCustomer implements ResolverInterface
{
    /**
     * Currency variable
     *
     * @var string
     */
    protected $currency;

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
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
            $environment = $this->emulate->startEnvironmentEmulation(
                $this->storeId,
                \Magento\Framework\App\Area::AREA_FRONTEND,
                true
            );
            // Setting currency /////////////////////////////////////////////////////
            $this->store->setCurrentCurrencyCode($this->currency);
            $wishlist = $this->wishlistProvider->loadByCustomerId($this->customerId, true);
            $wishListCollection = $wishlist->getItemCollection();
            // Applying pagination //////////////////////////////////////////////////
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $wishListCollection->getSize();
                $pageSize = $this->helperCatalog->getPageSize();
                $wishListCollection->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
            // Getting wishlist data /////////////////////////////////////////////////
            $wishList = $this->getWishListData($wishListCollection);
            $this->returnArray["success"] = true;
            $this->returnArray["wishList"] = $wishList;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * GetWishListData Function to get wishlist data
     *
     * @param \Magento\Wishlist\Model\Wishlist $wishListCollection wishListCollection
     *
     * @return array*
     */
    protected function getWishListData($wishListCollection)
    {
        $wishList = [];
        foreach ($wishListCollection as $item) {
            $product = $this->productFactory->create()->load($item->getProductId());
             //getting grouped product minimal price//
            if ($product->getTypeId() == 'grouped') {
                $count = 0;
                $usedProds = $product->getTypeInstance(true)->getAssociatedProducts($product);
                foreach ($usedProds as $child) {
                    if ($child->getId() != $product->getId()) {
                        if ($count == 0) {
                            $lowestprice = $child->getPrice();
                        }
                        if ($child->getPrice() < $lowestprice) {
                            $lowestprice = $child->getPrice();
                        }
                    }
                    $count++;
                }
                $product->setMinimalPrice($lowestprice);
            }
            $eachWishData = [];
            $eachWishData["id"] = $item->getId();
            $eachWishData["sku"] = $product->getSku();
            $eachWishData["qty"] = $item->getQty() * 1;
            $eachWishData["name"] = $product->getName();
            $eachWishData["price"] = $this->helperCatalog->stripTags(
                $this->pricingHelper->currency($product->getFinalPrice())
            );
            $eachWishData["productId"] = $product->getId();
            $eachWishData["thumbNail"] = $this->helperCatalog->getImageUrl($product, $this->width/3);
            $eachWishData["description"] = $item->getDescription();
            $options = $this->productConfigurationHelper->getOptions($item);
            $eachWishData["options"] = [];
            if (count($options) > 0) {
                foreach ($options as $option) {
                    $eachOption = [];
                    $eachOption["label"] = htmlspecialchars_decode($option["label"]);
                    if (is_array($option["value"])) {
                        $eachOption["value"] = $option["value"];
                    } else {
                        $eachOption["value"][] = $this->helperCatalog->stripTags($option["value"]);
                    }
                    $eachWishData["options"][] = $eachOption;
                }
            }
            $productData = $this->helperCatalog->getOneProductRelevantData(
                $product,
                $this->storeId,
                $this->width,
                $this->customerId
            );
            $customOption = [];
            foreach ($item->getOptions() as $option) {
                $customOption[$option->getCode()] = $option->getValue();
            }
            $wishList[] = $this->mergeWishProdData($eachWishData, $productData);
        }
        return $wishList;
    }

    /**
     * MergeWishProdData function
     *
     * @param mixed $wishData
     * @param mixed $productData
     * @return void
     */
    protected function mergeWishProdData($wishData, $productData)
    {
        return array_merge($wishData, $productData);
    }

    /**
     * CalculatePrice function
     *
     * @param mixed $product
     * @param mixed $customOption
     * @return void
     */
    protected function calculatePrice($product, $customOption)
    {
        $value = 0.0;
        $typeInstance = $product->getTypeInstance();
        $associatedProducts = $typeInstance
            ->setStoreFilter($product->getStore(), $product)
            ->getAssociatedProducts($product);
        foreach ($associatedProducts as $product) {
            $optionValue = $customOption["associated_product_" . $product->getId()] ?? null;
            if (!$optionValue) {
                continue;
            }
            $finalPrice = $product->getPriceInfo()
                ->getPrice(FinalPrice::PRICE_CODE)
                ->getValue();
            $value += $finalPrice * $optionValue;
        }
        return $value;
    }

    /**
     * Verify Request function to verify the request
     *
     * @return void|jSon
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->currency = $this->wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
            $this->pageNumber = $this->wholeData["pageNumber"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
