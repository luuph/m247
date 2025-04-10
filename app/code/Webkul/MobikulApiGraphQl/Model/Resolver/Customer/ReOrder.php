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
 * ReOrder resolver
 */
class ReOrder extends AbstractCustomer implements ResolverInterface
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
            $order = $this->order->loadByIncrementId($this->incrementId);
            if ($order->getCustomerId() != $this->customerId) {
                $this->returnArray["message"] = __("Invalid Order.");
                return $this->returnArray;
            }
            $reorderOutput = $this->reorder->execute($this->incrementId, (string)$this->storeId);
            $this->checkoutSession->setQuoteId($reorderOutput->getCart()->getId());

            $errors = $reorderOutput->getErrors();
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->returnArray['message'] = $error->getMessage();
                }
                $this->returnArray["success"] = false;
                $this->emulate->stopEnvironmentEmulation($environment);
                return $this->returnArray;
            }
            
            $this->returnArray["cartCount"] = (int) $this->helper->getCartCount($reorderOutput->getCart());
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Product(s) has been added to cart.");
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
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->incrementId = $this->wholeData["incrementId"] ?? "";
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
