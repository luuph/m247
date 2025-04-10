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
use Magento\Downloadable\Model\Link\Purchased\Item as PurchaseItem;
use Magento\Downloadable\Helper\Download as DownloadHelper;

/**
 * DownloadProduct resolver
 */
class DownloadProduct extends AbstractCustomer implements ResolverInterface
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
            $linkPurchasedItem = $this->purchasedLinkItem->load($this->hash, "link_hash");
            if (!$linkPurchasedItem->getId()) {
                $this->returnArray["message"] = __("Requested link does not exist.");
                return $this->returnArray;
            }
            $downloadableHelper = $this->downloadableHelper;
            if (!$downloadableHelper->getIsShareable($linkPurchasedItem)) {
                $linkPurchased = $this->purchasedlink->load($linkPurchasedItem->getPurchasedId());
                if ($linkPurchased->getCustomerId() != $this->customerId) {
                    $this->returnArray["message"] = __("Requested link does not exist.");
                    return $this->returnArray;
                }
            }
            $status = $linkPurchasedItem->getStatus();
            $downloadsLeft = $linkPurchasedItem->getNumberOfDownloadsBought() -
                $linkPurchasedItem->getNumberOfDownloadsUsed();
            $downloadableHelperFile = $this->downloadableFileHelper;
            if ($status == PurchaseItem::LINK_STATUS_AVAILABLE &&
                ($downloadsLeft || $linkPurchasedItem->getNumberOfDownloadsBought() == 0)
            ) {
                $resource = "";
                $resourceType = "";
                if ($linkPurchasedItem->getLinkType() == DownloadHelper::LINK_TYPE_URL) {
                    $this->returnArray["url"] = $linkPurchasedItem->getLinkUrl();
                    $buffer = $this->fileDriver->fileGetContents($this->returnArray["url"]);
                    $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
                    $this->returnArray["mimeType"] = $fileInfo->buffer($buffer);
                    $fileArray = explode(DS, $this->returnArray["url"]);
                    $this->returnArray["fileName"] = end($fileArray);
                } elseif ($linkPurchasedItem->getLinkType() == DownloadHelper::LINK_TYPE_FILE) {
                    $linkFile = $downloadableHelperFile->getFilePath(
                        $this->downloadableLink->getBasePath(),
                        $linkPurchasedItem->getLinkFile()
                    );
                    $linkFile = $this->helperCatalog->getBasePath().DS.$linkFile;
                    if ($this->fileDriver->isExists($linkFile)) {
                        $this->returnArray["mimeType"] = mime_content_type($linkFile);
                        $this->returnArray["url"] = $this->storeManager->getStore()
                            ->getUrl("mobikulhttp/download/index", ["hash"=>$this->hash]);
                        $linkFileArr = explode(DS, $linkFile);
                        $this->returnArray["fileName"] = end($linkFileArr);
                    } else {
                        $this->returnArray["message"] = __(
                            "An error occurred while getting the requested content. Please contact the store owner."
                        );
                        return $this->returnArray;
                    }
                }
            } elseif ($status == PurchaseItem::LINK_STATUS_EXPIRED) {
                $this->returnArray["message"] = __("The link has expired.");
                return $this->returnArray;
            } elseif ($status == PurchaseItem::LINK_STATUS_PENDING ||
                $status == PurchaseItem::LINK_STATUS_PAYMENT_REVIEW
            ) {
                $this->returnArray["message"] = __("The link is not available.");
                return $this->returnArray;
            } else {
                $this->returnArray["message"] = __(
                    "An error occurred while getting the requested content. Please contact the store owner."
                );
                return $this->returnArray;
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
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->hash = $this->wholeData["hash"] ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
