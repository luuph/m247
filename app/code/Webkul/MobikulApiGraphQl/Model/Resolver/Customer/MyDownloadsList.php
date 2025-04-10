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
 * MyDownloadList resolver
 */
class MyDownloadsList extends AbstractCustomer implements ResolverInterface
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
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
            $purchased = $this->purchasedLinkCollection->addFieldToFilter(
                "customer_id",
                $this->customerId
            )->addOrder("created_at", "DESC");
            $purchasedIds = [];
            foreach ($purchased as $item) {
                $purchasedIds[] = $item->getId();
            }
            if (empty($purchasedIds)) {
                $purchasedIds = [null];
            }
            $purchasedItems = $this->purchasedLinkItemCollection
                ->addFieldToFilter("purchased_id", ["in"=>$purchasedIds])
                ->addFieldToFilter("status", [
                    "nin"=>[
                        \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING_PAYMENT,
                        \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PAYMENT_REVIEW
                    ]
                ])
                ->setOrder("item_id", "desc");

            $purchasedItems->getSelect()->group('order_item_id');
            // Applying pagination //////////////////////////////////////////////////
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $purchasedItems->getSize();
                $pageSize = $this->helperCatalog->getPageSize();
                $purchasedItems->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
            foreach ($purchasedItems as $item) {
                $item->setPurchased($purchased->getItemById($item->getPurchasedId()));
            }
            // Creating Downloads List //////////////////////////////////////////////
            $downloadsList = [];
            $block = $this->listProduct;
            foreach ($purchasedItems as $downloads) {
                $eachDownloads = [];
                $eachDownloads["incrementId"] = $incrementId = $downloads->getPurchased()->getOrderIncrementId();
                $order = $this->order->loadByIncrementId($incrementId);
                if ($order->getRealOrderId() > 0) {
                    $eachDownloads["isOrderExist"] = true;
                    $eachDownloads["message"] = "";
                } else {
                    $eachDownloads["isOrderExist"] = false;
                    $eachDownloads["message"] = __("Sorry This Order Does not Exist!!");
                }
                $eachDownloads["hash"] = $downloads->getLinkHash();
                $eachDownloads["date"] = $block->formatDate($downloads->getPurchased()->getCreatedAt());
                $eachDownloads["state"] = $downloads->getStatus() == 'expired'? 'downloaded':$order->getState();
                $eachDownloads["status"] = __(ucfirst($downloads->getStatus()));
                $eachDownloads["statusColorCode"] = $this->helper->getOrderStatusColorCode($downloads->getStatus());
                $eachDownloads["proName"] = $this->helperCatalog->stripTags($downloads->getPurchased()
                    ->getProductName());
                $eachDownloads["remainingDownloads"] = $block->getRemainingDownloads($downloads);
                $canReorder = false;
                if ($this->canReorder($order)) {
                    $canReorder = $this->canReorder($order);
                }
                $eachDownloads["canReorder"] = $canReorder;
                $downloadsList[] = $eachDownloads;
            }
            $this->returnArray["downloadsList"] = $downloadsList;
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
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->pageNumber = $this->wholeData["pageNumber"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
