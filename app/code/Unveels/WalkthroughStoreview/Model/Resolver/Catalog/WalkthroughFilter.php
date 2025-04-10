<?php
declare(strict_types=1);

namespace Unveels\WalkthroughStoreview\Model\Resolver\Catalog;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Webkul\MobikulApiGraphQl\Model\Resolver\Catalog\AbstractCatalog;

/**
 * WalkthroughFilter resolver
 */
class WalkthroughFilter extends AbstractCatalog implements ResolverInterface
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
        print_r($args);exit;
        try {
            $this->verifyRequest();
            $walkthroughCollection = $this->walkthroughFactory->create()->getCollection()
                ->addFieldToFilter('status', 1)
                ->setOrder('sort_order', 'ASC')
                ->addFieldToSelect(['image', 'title', 'description', 'color_code', 'store_id']);

            $walkthroughData = [];

            foreach ($walkthroughCollection as $walkthrough) {
                $data = [];
                $imagePath = $this->helper->getUrl("media") . $walkthrough->getImage();

                $data["title"] = $walkthrough->getTitle();
                $data["content"] = $walkthrough->getDescription();
                $data['image'] = $imagePath;
                $data['imageDominantColor'] = $this->helper->getDominantColor($this->helper->getBaseMediaDirPath() . $walkthrough->getImage());
                $data["colorCode"] = $walkthrough->getColorCode();
                $data["store_id"] = $walkthrough->getStoreId();
                $walkthroughData[] = $data;
            }

            $this->returnArray["walkthroughVersion"] = $this->helper->getConfigData(
                "mobikul/walkthrough/walkthrough_version"
            );
            $this->returnArray["walkthroughData"] = $walkthroughData;
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
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["store_id"] ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
