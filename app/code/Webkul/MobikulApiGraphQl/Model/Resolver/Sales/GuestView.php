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

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Sales;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * GuestView resolver
 */
class GuestView extends \Webkul\MobikulApiGraphQl\Model\Resolver\ApiController implements ResolverInterface
{
    /**
     * Emulate variable
     *
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulate;

    /**
     * JsonHelper variable
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * OrderFactory variable
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * PriceHelper variable
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * Type variable
     *
     * @var mixed
     */
    protected $type;

    /**
     * ZipCode variable
     *
     * @var mixed
     */
    protected $zipCode;
    
    /**
     * Constructor function
     *
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param \Magento\Store\Model\App\Emulation $emulate
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->emulate = $emulate;
        $this->jsonHelper = $jsonHelper;
        $this->orderFactory = $orderFactory;
        $this->priceHelper = $priceHelper;
        parent::__construct($helper, $request, $jsonHelper);
    }

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
        $this->verifyRequest();
        $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
        $errors = false;
        $order = $this->orderFactory->create();
        if (!empty($this->wholeData) && $this->incrementId && $this->type) {
            if (empty($this->incrementId) || empty($this->lastName) || empty($this->type) ||
                empty($this->storeId) || !in_array($this->type, ["email", "zip"]) ||
                $this->type == "email" && empty($this->email) || $this->type == "zip" && empty($this->zipCode)
            ) {
                $errors = true;
            }
            if (!$errors) {
                $order = $order->loadByIncrementIdAndStoreId($this->incrementId, $this->storeId);
            }
            $errors = true;
            if ($order->getId()) {
                $billingAddress = $order->getBillingAddress();
                if (strtolower($this->lastName) == strtolower($billingAddress->getLastname()) &&
                    (
                        $this->type == "email" &&
                        strtolower($this->email) == strtolower($billingAddress->getEmail()) ||
                        $this->type == "zip" &&
                        strtolower($this->zipCode) == strtolower($billingAddress->getPostcode())
                    )
                ) {
                    $errors = false;
                }

                if (!$errors) {
                    $items = $order->getItemsCollection();
                    $itemList = [];
                    $orderData = [];
                    $subtotal = 0;
                    $shipping = 0;
                    foreach ($items as $item) {
                        $eachItem = [];
                        $eachItem["name"] = htmlspecialchars_decode($item->getName());
                        $eachItem["sku"] = $item->getSku();
                        $eachItem["price"] = $this->priceHelper->currency($item->getPriceInclTax(), true, false);
                        $eachItem["qty"] = $item->getQtyOrdered()*1;
                        $eachItem["subTotal"] = $this->priceHelper->currency($item->getPriceInclTax(), true, false);
                        ;
                        $itemList[] = $eachItem;
                        $subtotal += $item->getPriceInclTax();
                        $shipping += $item->getShippingAmount();
                    }
                    $grandtotal = $subtotal+$shipping;
                    $orderData["itemList"] = $itemList;
                    $totals = [];

                    $eachTotal = [];
                    $eachTotal["code"] = 'subtotal';
                    $eachTotal["label"] = __('Subtotal');
                    $eachTotal["value"] = $subtotal;
                    $eachTotal["formattedValue"] = $this->priceHelper->currency($subtotal, true, false);
                    $totals[] = $eachTotal;

                    $eachTotal = [];
                    $eachTotal["code"] = 'shipping';
                    $eachTotal["label"] = __('Shipping & Handing');
                    $eachTotal["value"] = $shipping;
                    $eachTotal["formattedValue"] = $this->priceHelper->currency($shipping, true, false);
                    $totals[] = $eachTotal;

                    $eachTotal = [];
                    $eachTotal["code"] = 'grand_total';
                    $eachTotal["label"] = __('Grand Total');
                    $eachTotal["value"] = $grandtotal;
                    $eachTotal["formattedValue"] = $this->priceHelper->currency($grandtotal, true, false);
                    $totals[] = $eachTotal;

                    $orderData["totals"] = $totals;
                    $this->returnArray["orderData"] = $orderData;

                    $orderInfo = [];
                    $eachInfo['shippingAddress'] = $order->getShippingAddress()->toString();
                    $eachInfo['shippingMethod'] = $order->getShippingMethod();
                    $eachInfo['billingAddress'] = $order->getBillingAddress()->toString();
                    $eachInfo['paymentMethod'] = $order->getPayment()->getMethodInstance()->getTitle();
                    $orderInfo[] = $eachInfo;
                    $this->returnArray['orderData']['orderInfo'] = $orderInfo;
                }

            }
        }
      
        if (!$errors && $order->getId()) {
            $this->returnArray["success"] = true;
        } else {
            $this->returnArray["message"] = __("You entered incorrect data. Please try again.");
        }
        $this->emulate->stopEnvironmentEmulation($environment);
        return $this->returnArray;
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
            $this->type = $this->wholeData["type"] ?? "";
            $this->email = $this->wholeData["email"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->zipCode = $this->wholeData["zipCode"] ?? 0;
            $this->lastName = $this->wholeData["lastName"] ?? "";
            $this->incrementId = $this->wholeData["incrementId"] ?? 0;
            $authKey = $this->getRequest()->getHeader("authKey");
            $authData = $this->helper->isAuthorized($authKey);
            if ($authData["code"] != 1) {
                return $this->returnArray;
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
