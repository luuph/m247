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
 * SaveReview resolver
 */
class SaveReview extends AbstractCustomer implements ResolverInterface
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
            // Checking customer token //////////////////////////////////////////////
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "As customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
            }
            if ($this->customerId == 0) {
                $this->customerId = null;
            }
            $review = $this->review
                ->setEntityPkValue($this->productId)
                ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)
                ->setTitle($this->title)
                ->setDetail($this->detail)
                ->setEntityId(1)
                ->setStoreId($this->storeId);
            if ($this->customerId != 0) {
                $review->setCustomerId($this->customerId);
            }
            $review->setNickname($this->nickname)
                ->setReviewId($review->getId())
                ->setStores([$this->storeId])
                ->save();
            foreach ($this->ratings as $rating) {
                if (!empty($rating)) {
                    $this->ratingFactory->create()
                    ->setRatingId($rating['ratingId'])
                    ->setReviewId($review->getId())
                    ->setCustomerId($this->customerId)
                    ->addOptionVote($rating['optionId'], $this->productId);
                } else {
                    $this->returnArray["message"] = __("Please add the ratings.");
                    return $this->returnArray;
                }
            }
            $review->aggregate();
            $this->returnArray["message"] = __("Your review has been accepted for moderation.");
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
     * Verify Request function to verify the request
     *
     * @return void|jSon
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->title = $this->wholeData["title"] ?? "";
            $this->detail = $this->wholeData["detail"] ?? "";
            $this->ratings = $this->wholeData["ratings"] ?? [];
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->nickname = $this->wholeData["nickname"] ?? "";
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
