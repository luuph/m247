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

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Extra;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Logout resolver
 */
class Logout extends AbstractMobikul implements ResolverInterface
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
            $collection = $this->deviceTokenFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter("customer_id", $this->customerId)
                ->addFieldToFilter("token", $this->token);
            foreach ($collection as $eachToken) {
                $this->deviceTokenFactory->create()->load($eachToken->getId())->setCustomerId(0)->save();
            }
            $this->customerSession->unsetAll();
            $this->coreSession->unsetAll();
            // merging compare list /////////////////////////////////////////////////
            $this->productCompare->setAllowUsedFlat(false);
            $items = $this->compareCollection->create();
            $items->useProductItem(true)->setStoreId($this->storeId);
            $items->setVisitorId($this->visitor->getId());
            $attributes = $this->catalogConfig->getProductAttributes();
            $items->addAttributeToSelect($attributes)
                ->loadComparableAttributes()
                ->addMinimalPrice()
                ->addTaxPercents()
                ->setVisibility($this->productVisibility->getVisibleInSiteIds());
            foreach ($items as $item) {
                $this->compareItem->purgeVisitorByCustomer($item);
                $this->productCompare->calculate(true);
            }
            $this->returnArray["success"] = true;
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * VerifyRequest function
     *
     * @return void
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->token = $this->wholeData["token"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            /////// Checking customer token /////////////////////////////////////////
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "Customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
            } elseif ($this->customerId != 0) {
                $this->customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
