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

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Watch;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Qr resolver
 */
class Qr extends AbstractWatch implements ResolverInterface
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
            $this->returnArray["success"] = false;
            if($this->firebaseId != "" && $this->os != ""){
                $encFirebaseId = $this->encrypted->encrypt($this->firebaseId);
                $qr_url = self::QRBASEURL."?chs=".$this->width."x".$this->width
                ."&cht=qr&chl=".$encFirebaseId;
                $watchFactory = $this->watchFactory->create();
                $filteredWatchFactoryData = $watchFactory->getCollection()
                    ->addFieldToFilter("firebase_id", $this->firebaseId)
                    ->getFirstItem();
                if($filteredWatchFactoryData != null && $filteredWatchFactoryData->getId() != null){
                    $filteredWatchFactoryData->setQrUrl($qr_url);
                    $filteredWatchFactoryData->setOs($this->os);
                    $filteredWatchFactoryData->setEncfirebaseId($encFirebaseId);
                    $filteredWatchFactoryData->save();
                }
                else{
                    $watchFactory->setQrUrl($qr_url);
                    $watchFactory->setOs($this->os);
                    $watchFactory->setFirebaseId($this->firebaseId);
                    $watchFactory->setEncfirebaseId($encFirebaseId);
                    $watchFactory->save();
                }

                $this->returnArray["success"] = true;
                $this->returnArray["qr_url"] = $qr_url;
                $this->returnArray["message"] = __("Qr code generated successfully.");
            }
            else{
                $this->returnArray["message"] = __("firebaseId and os fields are required.");
            }
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
            $this->firebaseId = $this->wholeData["firebaseId"] ?? "";
            $this->os = $this->wholeData["os"] ?? "";
            $this->width = $this->wholeData["width"] ?? 60;
            if($this->width < 60){
                $this->width = 60;
            }
            elseif($this->width > 500){
                $this->width = 500;
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
