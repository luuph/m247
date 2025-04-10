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
 * RatingFormData resolver
 */
class RatingFormData extends AbstractCatalog implements ResolverInterface
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
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $ratingFormData = [];
            $ratingCollection = $this->rating->getResourceCollection()->addEntityFilter(
                'product'
            )->setPositionOrder()->addRatingPerStoreName(
                $this->storeId
            )->setStoreFilter(
                $this->storeId
            )->setActiveFilter(
                true
            )->load()->addOptionToItems();
            
            foreach ($ratingCollection as $rating) {
                $eachTypeRating = [];
                $eachRatingFormData = [];
                foreach ($rating->getOptions() as $option) {
                    $eachTypeRating[] = $option->getId();
                }
                $eachRatingFormData["id"] = $rating->getId();
                $eachRatingFormData["name"] = $this->helperCatalog->stripTags($rating->getRatingCode());
                $eachRatingFormData["values"] = $eachTypeRating;
                $ratingFormData[] = $eachRatingFormData;
            }
            $this->returnArray["ratingFormData"] = $ratingFormData;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->returnArray["success"] = true;
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
            $this->storeId = $this->wholeData["storeId"] ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
