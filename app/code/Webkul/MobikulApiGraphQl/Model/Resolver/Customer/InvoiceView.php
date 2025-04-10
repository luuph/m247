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
 * InvoiceView resolver
 */
class InvoiceView extends AbstractCustomer implements ResolverInterface
{
    /**
     * InvoiceId variable
     *
     * @var mixed
     */
    protected $invoiceId;

    /**
     * Invoice variable
     *
     * @var \Magento\Sales\Api\Data\InvoiceInterface
     */
    protected $invoice;

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
            $this->invoice = $this->invoiceRepository->get($this->invoiceId);
            $this->loadedOrder = $this->invoice->getOrder();
            $this->coreRegistry->register('current_order', $this->loadedOrder);
            $this->coreRegistry->register('current_invoice', $this->invoice);
            $this->returnArray["orderId"] = (int)$this->loadedOrder->getId();
            // Get Invoice Item Details /////////////////////////////////////////////
            $this->itemBlock = $this->orderItemRenderer;
            $this->priceBlock = $this->priceRenderer;
            $invoiceItems = $this->invoice->getItemsCollection();
            $items = $this->invoiceItemCollectionFactory->create()->setInvoiceFilter($this->invoiceId);
            $this->getInvoiceItemsData($items);
            $this->getInvoiceTotalsInformation();

            $this->returnArray['shippingAddress'] = new \Magento\Framework\DataObject();
            $shippingAddress = $this->loadedOrder->getShippingAddress();

            if (is_object($shippingAddress)) {
                $this->returnArray['shippingAddress']['firstname'] = $shippingAddress->getFirstname();
                $this->returnArray['shippingAddress']['lastname'] = $shippingAddress->getLastname();
                $this->returnArray['shippingAddress']['city'] = $shippingAddress->getCity();
                $this->returnArray['shippingAddress']['region'] = $shippingAddress->getRegion();
                $this->returnArray['shippingAddress']['street'] = $shippingAddress->getStreet();
                $this->returnArray['shippingAddress']['country'] = $this->country->loadByCode(
                    $shippingAddress->getCountryId()
                )->getName();
                $this->returnArray['shippingAddress']['pincode'] = $shippingAddress->getPostCode();
            }

            $billingAddress = $this->loadedOrder->getBillingAddress();
            $this->returnArray['billingAddress']['firstname'] = $billingAddress->getFirstname();
            $this->returnArray['billingAddress']['lastname'] = $billingAddress->getLastname();
            $this->returnArray['billingAddress']['city'] = $billingAddress->getCity();
            $this->returnArray['billingAddress']['region'] = $billingAddress->getRegion();
            $this->returnArray['billingAddress']['street'] = $billingAddress->getStreet();
            $this->returnArray['billingAddress']['country'] = $this->country->loadByCode(
                $billingAddress->getCountryId()
            )->getName();
            $this->returnArray['billingAddress']['pincode'] = $billingAddress->getPostCode();

            $this->returnArray['shippingMethod']['title'] = $this->loadedOrder->getShippingDescription();

            $payment = $this->loadedOrder->getPayment();
            $method = $payment->getMethodInstance();
            $this->returnArray['paymentMethod']['title'] = $method->getTitle();
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["success"] = false;
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * Function verifyRequest
     *
     * Verify and validate request
     *
     * @return json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->invoiceId = $this->wholeData["invoiceId"] ?? 0;
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
        } else {
            throw new \BadMethodCallExceptionc(__("Invalid Request"));
        }
    }

    /**
     * Function to get Invoice Items data
     *
     * @param Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory $items items
     *
     * @return void
     */
    public function getInvoiceItemsData($items)
    {
        if (count($items) > 0) {
            $itemsArray = [];
            foreach ($items as $item) {
                $this->itemBlock->setItem($item);
                $this->priceBlock->setItem($item);
                if (in_array($this->itemBlock->getSku(), $itemsArray)) {
                    continue;
                } else {
                    $itemsArray[] = $this->itemBlock->getSku();
                }
                $eachItem = [];
                $eachItem["id"] = $item->getId();
                $eachItem["name"] = $item->getName();
                $eachItem["productId"] = $item->getProductId();
                $eachItem["sku"] = $this->itemBlock->prepareSku($this->itemBlock->getSku());
                if ($options = $this->itemBlock->getItemOptions()) {
                    $eachItem["option"] = $this->getItemData($options);
                } else {
                    $eachItem["option"] = [];
                }
                $eachItem["qty"] = $item->getQty()*1;
                $eachItem["taxAmount"] = strip_tags($this->invoiceItemRenderer->displayPriceAttribute("tax_amount"));
                $eachItem["discountAmount"] = strip_tags(
                    $this->invoiceItemRenderer->formatPrice($item->getDiscountAmount())
                );
                if ($this->adminPriceRenderer->displayBothPrices() || $this->adminPriceRenderer->displayPriceExclTax()
                ) {
                    $eachItem["price"] = strip_tags($this->adminPriceRenderer->getUnitPriceExclTaxHtml());
                    $eachItem["subTotal"] = strip_tags($this->adminPriceRenderer->getRowPriceExclTaxHtml());
                }
                if ($this->adminPriceRenderer->displayBothPrices() || $this->adminPriceRenderer->displayPriceInclTax()
                ) {
                    $eachItem["price"] = strip_tags($this->adminPriceRenderer->getUnitPriceInclTaxHtml());
                    $eachItem["subTotal"] = strip_tags($this->adminPriceRenderer->getRowPriceInclTaxHtml());
                }
                $eachItem["rowTotal"] = strip_tags(
                    $this->adminPriceRenderer->displayPrices(
                        $this->adminPriceRenderer->getBaseTotalAmount($item),
                        $this->adminPriceRenderer->getTotalAmount($item)
                    )
                );
                $this->returnArray["itemList"][] = $eachItem;
            }
        }
    }

    /**
     * GetItemData function
     *
     * @param mixed $options
     * @return void
     */
    public function getItemData($options)
    {
        $eachItem = [];
        foreach ($options as $option) {
            $value = null;
            $eachOption = [];
            $eachOption["label"] = $this->itemBlock->escapeHtml($option["label"]);
            if (!$this->itemBlock->getPrintStatus()) {
                $formatedOptionValue = $this->itemBlock->getFormatedOptionValue($option);
                if (isset($formatedOptionValue["full_view"])) {
                    $value = $formatedOptionValue["full_view"];
                } else {
                    $value = $formatedOptionValue["value"];
                }
            } else {
                $value = nl2br($this->itemBlock->escapeHtml(
                    (isset($option["print_value"]) ? $option["print_value"] : $option["value"])
                ));
            }
            if (!is_array($value)) {
                $eachOption["value"][] = $value;
            } else {
                $eachOption["value"] = $value;
            }
            $eachItem[] = $eachOption;
        }
        return $eachItem;
    }

    /**
     * Function to get totals information
     *
     * @return void
     */
    public function getInvoiceTotalsInformation()
    {
        $this->invoiceTotals->_initTotals();
        $footerTotals = $this->invoiceTotals->getTotals('footer');
        if ($footerTotals) {
            foreach ($footerTotals as $total) {
                $eachItem["code"] = $this->invoiceTotals->escapeHtml($total->getCode());
                $eachItem["label"] = $this->invoiceTotals->escapeHtml($total->getLabel());
                $eachItem["value"] = $total->getValue();
                $eachItem["formattedValue"] = strip_tags($this->invoiceTotals->formatValue($total));
                $this->returnArray["totals"][] = $eachItem;
            }
        }
        $invoiceTotals = $this->invoiceTotals->getTotals("");
        if ($invoiceTotals) {
            foreach ($invoiceTotals as $total) {
                $label = $total->getLabel();
                $eachItem["code"] = $this->invoiceTotals->escapeHtml($total->getCode());
                $eachItem["label"] = $this->invoiceTotals->escapeHtml($label);
                $eachItem["value"] = $total->getValue();
                $eachItem["formattedValue"] = strip_tags($this->invoiceTotals->formatValue($total));
                $this->returnArray["totals"][] = $eachItem;
                if ($label == "Grand Total") {
                    $this->returnArray["cartTotal"] = strip_tags($this->invoiceTotals->formatValue($total));
                }
            }
        }
    }
}
