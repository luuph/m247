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
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * UploadBannerPic resolver
 */
class UploadBannerPic extends AbstractCustomer implements ResolverInterface
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
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->mFactor = $this->wholeData["mFactor"] ?? 1;
            $this->mFactor = $this->helper->calcMFactor($this->mFactor);
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->wholeData['imageName'] = $this->wholeData['imageName'] ?? "";
            $this->wholeData['imageEncoded'] = $this->wholeData['imageEncoded'] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "As customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
                return $this->returnArray;
            }
            if ($this->wholeData['imageName'] == "") {
                $this->returnArray["message"] = __(
                    "Image name is required. Please upload image."
                );
                return $this->returnArray;
            }
            if ($this->wholeData['imageEncoded'] == "") {
                $this->returnArray["message"] = __(
                    "Image is required. Please upload image."
                );
                return $this->returnArray;
            }
            if (count(explode(',', $this->wholeData['imageEncoded'])) < 2) {
                $this->returnArray["message"] = __(
                    "Image is required. Please upload image."
                );
                return $this->returnArray;
            }

            $extension = explode(".", $this->wholeData['imageName']);
            $this->wholeData['imageName'] = $this->customerId."-banner-".time().".".end($extension);

            $mediaPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            $originalPath = 'mobikul'.DS.'customerpicture'.DS.$this->customerId.DS.'banner'.DS;
            $mediaFullPath = $mediaPath . $originalPath;
            if (!$this->fileDriver->isDirectory($mediaFullPath)) {
                $this->ioFile->mkdir($mediaFullPath);
            }
            /* Check File is exist or not */
            $fullFilepath = $mediaFullPath . $this->wholeData['imageName'];
            $imageEncoded = explode(',', $this->wholeData['imageEncoded']);
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $fileContent = base64_decode($imageEncoded[1]);
            // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
            $savedFile = $this->fileDriver->fileOpen($mediaFullPath . $this->wholeData['imageName'], "wb");
            $this->fileDriver->fileWrite($savedFile, $fileContent);
            $this->fileDriver->fileClose($savedFile);

            $userImageModel = $this->userImageFactory->create();
            $collection = $userImageModel->getCollection()->addFieldToFilter("customer_id", $this->customerId);
            if ($collection->getSize() > 0) {
                foreach ($collection as $value) {
                    $loadedUserImageModel = $userImageModel->load($value->getId());
                    $loadedUserImageModel->setBanner($this->wholeData['imageName']);
                    $loadedUserImageModel->save();
                }
            } else {
                $userImageModel->setBanner($this->wholeData['imageName']);
                $userImageModel->setCustomerId($this->customerId)->save();
            }
            $this->returnArray = $this->uploadHelper->resizeAndCache(
                $this->width,
                $this->customerId,
                $this->mFactor,
                "banner"
            );
            $this->returnArray['success'] = true;
            $this->returnArray["message"] = "Banner uploaded successfully.";
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }
}
