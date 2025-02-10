<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_PdfInvoice
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoice\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Items\AbstractItems;
use Magento\Tax\Helper\Data;
use Mageplaza\PdfInvoice\Helper\Data as HelperData;
use Mageplaza\PdfInvoice\Model\Source\Type;
use Magento\Catalog\Helper\Image;
use Magento\GiftMessage\Helper\Message;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\Order\CreditmemoRepository;
use Magento\Sales\Model\Order\ShipmentRepository;

/**
 * Class PdfItems
 * @package Mageplaza\PdfInvoice\Block
 */
abstract class PdfItems extends AbstractItems
{
    public const BUNDLE_BLOCK             = 'Mageplaza\PdfInvoice\Block\BundleItems';
    public const DEFAULT_BUNDLE_TEMPLATE  = 'Mageplaza_PdfInvoice::handle/';
    public const ORDER_BUNDLE_TEMPLATE    = 'Mageplaza_PdfInvoice::handle/order/';
    public const SHIPMENT_BUNDLE_TEMPLATE = 'Mageplaza_PdfInvoice::handle/shipment/';
    public const BUNDLE_ITEM              = 'bundle';

    /**
     * @var HelperData
     */
    protected $helperConfig;

    /**
     * @var Data
     */
    protected $taxHelper;

    /**
     * @var $storeId
     */
    protected $storeId;

    /**
     * @var $item
     */
    protected $item;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Image
     */
    protected $helperImage;

    /**
     * @var Message
     */
    protected $helperMessage;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * @var CreditmemoRepository
     */
    protected $creditmemoRepository;

    /**
     * @var ShipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @var $classForEl
     */
    protected $classForEl;

    /**
     * @var $codeValue
     */
    protected $codeValue;

    /**
     * @var $impresive
     */
    protected $impresive;

    /**
     * PdfItems constructor.
     *
     * @param Context $context
     * @param Data $taxHelper
     * @param HelperData $helperdata
     * @param Image $helperImage
     * @param Message $helperMessage
     * @param OrderRepository $orderRepository
     * @param InvoiceRepository $invoiceRepository
     * @param CreditmemoRepository $creditmemoRepository
     * @param ShipmentRepository $shipmentRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $taxHelper,
        HelperData $helperdata,
        Image $helperImage,
        Message $helperMessage,
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository,
        CreditmemoRepository $creditmemoRepository,
        ShipmentRepository $shipmentRepository,
        array $data = []
    ) {
        $this->taxHelper  = $taxHelper;
        $this->helperData = $helperdata;
        $this->helperImage = $helperImage;
        $this->helperMessage = $helperMessage;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->shipmentRepository = $shipmentRepository;

        parent::__construct($context, $data);
    }

    /**
     * Get item
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set Item
     *
     * @param $item
     */
    public function setItem($item)
    {
        $this->item    = $item;
        $this->storeId = $item->getStoreId();
    }

    /**
     * Set variable
     *
     * @param $classForEl
     * @param $item
     * @param $codeValue
     * @return void
     */
    public function setVariable($classForEl, $item , $codeValue, $type=null)
    {
        $this->classForEl = $classForEl;
        $this->item = $item;
        $this->codeValue = $codeValue;
        $this->type = $type;
    }

    public function setImpresive($value)
    {
        $this->impresive = $value;
    }

    public function getImpresive()
    {
        return $this->impresive;
    }

    /**
     * Get class for element
     * @return string
     */
    public function getClassForEl()
    {
        return $this->classForEl;
    }

    /**
     * Get Code
     * @return mixed
     */
    public function getCode()
    {
        return $this->codeValue;
    }

    /**
     * Get type
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get type item
     * @return string
     */
    public function getTypeItem()
    {
        return $this->_getItemType($this->getItem());
    }

    /**
     * Is bundle item
     * @return bool
     */
    public function isBundleItem()
    {
        return $this->getTypeItem() == self::BUNDLE_ITEM ? true : false;
    }

    /**
     * @param $item
     * @param $order
     * @param $type
     * @param $indexKey
     * @param string $codeType
     * @param mixed $barcodeConfig
     * @param int $isBarcode
     * @param null $bundleFile
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function renderBundleItem($item, $order, $type, $indexKey, $codeType, $barcodeConfig, $isBarcode = 0, $bundleFile = null, $columnsConfig = [])
    {
        if ($type == Type::ORDER) {
            $template = self::ORDER_BUNDLE_TEMPLATE . $bundleFile;
        } elseif ($type == Type::SHIPMENT) {
            $template = self::SHIPMENT_BUNDLE_TEMPLATE . $bundleFile;
        } else {
            $template = self::DEFAULT_BUNDLE_TEMPLATE . $bundleFile;
        }

        return $this->getLayout()
            ->createBlock(self::BUNDLE_BLOCK)
            ->setItem($item)
            ->setOrder($order)
            ->setType($type)
            ->setIndexKey($indexKey)
            ->setCodeType($codeType)
            ->setBarcodeConfig($barcodeConfig)
            ->setIsBarcode($isBarcode)
            ->setColumnsConfig($columnsConfig)
            ->setPageSize($this->getPageSize())
            ->setTemplate($template)
            ->toHtml();
    }

    /**
     * @return array
     */
    public function getItemOptions()
    {
        $result = [];
        if ($options = $this->getItem()->getOrderItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

    /**
     * @param string|array $value
     *
     * @return string
     */
    public function getValueHtml($value)
    {
        if (is_array($value)) {
            return sprintf(
                '%d',
                $value['qty']
            ) . ' x ' . $this->escapeHtml(
                $value['title']
            ) . " " . $this->getItem()->getOrder()->formatPrice(
                    $value['price']
                );
        }

        return $this->escapeHtml($value);
    }

    /**
     * @param mixed $item
     *
     * @return mixed
     */
    public function getSku($item)
    {
        if ($item->getOrderItem()->getProductOptionByCode('simple_sku')) {
            return $item->getOrderItem()->getProductOptionByCode('simple_sku');
        }

        return $item->getSku();
    }

    /**
     * @return bool|BlockInterface
     * @throws LocalizedException
     */
    public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }

    /**
     * @param $item
     *
     * @return string
     * @throws LocalizedException
     */
    public function getItemPrice($item)
    {
        $block = $this->getLayout()->getBlock('item_price');
        $block->setItem($item);

        return $block->toHtml();
    }

    /**
     * Return whether display setting is to display price including tax
     *
     * @return bool
     */
    public function displayPriceInclTax()
    {
        return $this->taxHelper->displaySalesPriceInclTax($this->storeId);
    }

    /**
     * Return whether display setting is to display price excluding tax
     *
     * @return bool
     */
    public function displayPriceExclTax()
    {
        return $this->taxHelper->displaySalesPriceExclTax($this->storeId);
    }

    /**
     * Return whether display setting is to display both price including tax and price excluding tax
     *
     * @return bool
     */
    public function displayBothPrices()
    {
        return $this->taxHelper->displaySalesBothPrices($this->storeId);
    }

    /**
     * @return mixed
     */
    public function getPageSize()
    {
        return $this->helperData->getPageSize();
    }

    /**
     * @return Image
     */
    public function getHelperImage()
    {
        return $this->helperImage;
    }

    /**
     * @return Message
     */
    public function getHelperMessage()
    {
        return $this->helperMessage;
    }

    /**
     * @return OrderRepository|null
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrder()
    {
        $order = $this->getData('order');

        if ($order !== null) {
            return $order;
        }
        $orderId = (int)$this->getData('order_id');
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            $this->setData('order', $order);
        }

        return $this->getData('order');
    }

    /**
     * @return InvoiceRepository|null
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getInvoice()
    {
        $invoice = $this->getData('invoice');
        if ($invoice !== null) {
            return $invoice;
        }

        $invoiceId = (int)$this->getData('invoice_id');
        if ($invoiceId) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            $this->setData('invoice', $invoice);
        }

        return $this->getData('invoice');
    }

    /**
     * @return CreditmemoRepository|null
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCreditmemo()
    {
        $creditmemo = $this->getData('creditmemo');
        if ($creditmemo !== null) {
            return $creditmemo;
        }

        $creditmemoId = (int)$this->getData('creditmemo_id');
        if ($creditmemoId) {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
            $this->setData('creditmemo', $creditmemo);
        }

        return $this->getData('creditmemo');
    }

    /**
     * @return ShipmentRepository|null
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getShipment()
    {
        $shipment = $this->getData('shipment');
        if ($shipment !== null) {
            return $shipment;
        }

        $shipmentId = (int)$this->getData('shipment_id');
        if ($shipmentId) {
            $shipment = $this->shipmentRepository->get($shipmentId);
            $this->setData('shipment', $shipment);
        }

        return $this->getData('shipment');
    }

    /**
     * Get barcode config
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getBarcodeConfig($storeId = null)
    {
        return $this->helperData->getBarcodeConfig($storeId);
    }

    /**
     * Get barcode type
     *
     * @param string $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function getCodeType($type, $storeId = null)
    {
        return $this->helperData->getCodeType($type, $storeId);
    }

    /**
     * @param mixed $item
     * @param null $storeId
     *
     * @return mixed
     */
    public function getCodeValue($item, $storeId)
    {
        $barcodeConfig = $this->getBarcodeConfig($storeId);
        $codeValue = [];

        if ($barcodeConfig['barcode_attribute'] === 'sku') {
            $codeValue['barcode'] = $this->getSku($item);
        } else if ($barcodeConfig['barcode_attribute'] === 'product_id') {
            $codeValue['barcode'] = $item->getProductId();
        } else {
            $codeValue['barcode'] = $item->getName();
        }

        if ($barcodeConfig['qrcode_attribute'] === 'sku') {
            $codeValue['qrcode'] = $this->getSku($item);
        } else if ($barcodeConfig['qrcode_attribute'] === 'product_id') {
            $codeValue['qrcode'] = $item->getProductId();
        } else {
            $codeValue['qrcode'] = $item->getName();
        }

        return $codeValue;
    }

    /**
     * Get number columns
     *
     * @param mixed $item
     * @param null $storeId
     *
     * @return mixed
     */
    public function getNumberColumn($columnsConfig, $defaultNumber)
    {
        foreach ($columnsConfig as $column) {
            if ($column['name'] === 'Tax' || $column['name'] === 'Discount') {
                if ($column['status']) {
                    $defaultNumber++;
                }
            }
        }
        switch ($defaultNumber) {
            case 1: return 'one';
            case 2: return 'two';
            case 3: return 'three';
            case 4: return 'four';
            case 5: return 'five';
            case 6: return 'six';
        }
    }

    /**
     * Get column config
     *
     * @return array|array[]|mixed
     */
    public function getColumnsConfig($type)
    {
        if ($templateColumnsConfig = $this->_request->getParam('templateColumnsConfig')) {
            return json_decode($templateColumnsConfig, true);
        }
        if ($templateId = $this->_request->getParam('templateId')) {
            return $this->helperData->getColumnsById($templateId, $type);
        }
        return $this->helperData->getColumns($type);
    }

    /**
     * Get header columns
     *
     * @param $key
     * @param $a4Barcode
     * @return string
     */
    public function getThHtml($key, $a4Barcode)
    {
        if ($key === 'Items') {
            if ($a4Barcode) {
                return <<<HTML
                    <div class="mp-item-bc">
                        <span>$key</span>
                    </div>
                    <div class="mp-barcode-bc">
                        <span></span>
                    </div>
                HTML;
            } else {
                return <<<HTML
                    <div class="mp-item">
                        <span>$key</span>
                    </div>
                HTML;
            }
        }
        $classForEl = $a4Barcode ? 'mp-'.strtolower($key).'-bc' : 'mp-'.strtolower($key);
        return <<<HTML
            <div class="$classForEl">
                <span>$key</span>
            </div>
        HTML;
    }

    /**
     * Get value columns
     *
     * @param $key
     * @param $a4Barcode
     * @return string
     */
    public function getThImpresiveHtml($key, $a4Barcode)
    {
        if ($key === 'Items') {
            if ($a4Barcode) {
                return <<<HTML
                    <th style="text-align:right" class="mp-item-bc">
                        <span>Item Descriptions<span>
                    </th>
                    <th class="mp-barcode-bc">
                        <span></span>
                    </th>
                HTML;
            } else {
                return <<<HTML
                    <th style="text-align:center" class="mp-item">
                        <span>Item Descriptions<span>
                    </th>
                HTML;
            }
        }
        $classForEl = $a4Barcode ? 'mp-'.strtolower($key).'-bc' : 'mp-'.strtolower($key);
        return <<<HTML
            <th class="$classForEl">
                <span>$key</span>
            </th>
        HTML;
    }

    /**
     * Get value for div
     *
     * @param $key
     * @param $a4Barcode
     * @param $item
     * @param $codeValue
     * @return string|void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTdHtml($key, $a4Barcode, $item, $codeValue, $type)
    {
        $classForEl = $a4Barcode ? 'mp-'.strtolower($key).'-bc' : 'mp-'.strtolower($key);
        if ($key === 'Items') $classForEl = $a4Barcode ? 'mp-item-bc' : 'mp-item';
        $qty        = $item->getQty() * 1;
        if ($type === 'order') {
            $qty = $item->getQtyOrdered() * 1;
        }
        switch ($key) {
            case 'Items':
                $this->setVariable($classForEl, $item, $codeValue, $type);
                return $this->getChildHtml('column_items',false);
            case 'Qty':
                return <<<HTML
                    <div class="$classForEl">
                        <span>$qty</span>
                    </div>
                HTML;
            case 'Price':
                $this->setVariable($classForEl, $item, $codeValue);
                return $this->getChildHtml('column_price', false);
            case 'Subtotal':
                $this->setVariable($classForEl, $item, $codeValue);
                return $this->getChildHtml('column_sub_total', false);
            case 'Tax':
                $order = $this->getOrder();
                $tax   = $order->formatPrice($item->getTaxAmount());
                return <<<HTML
                    <div class="$classForEl">
                        $tax
                    </div>
                HTML;
            case 'Discount':
                $order = $this->getOrder();
                $discount   = $order->formatPrice($item->getDiscountAmount());
                return <<<HTML
                    <div class="$classForEl">
                        $discount
                    </div>
                HTML;
        }
    }

    /**
     * Get value for table
     *
     * @param $key
     * @param $a4Barcode
     * @param $item
     * @param $codeValue
     * @return string|void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTdImpresiveHtml($key, $a4Barcode, $item, $codeValue, $type)
    {
        $classForEl = $a4Barcode ? 'mp-'.strtolower($key).'-bc' : 'mp-'.strtolower($key);
        if ($key === 'Items') $classForEl = $a4Barcode ? 'mp-item-bc' : 'mp-item';
        $qty        = $item->getQty() * 1;
        if ($type === 'order') {
            $qty = $item->getQtyOrdered() * 1;
        }
        switch ($key) {
            case 'Items':
                $this->setImpresive(true);
                $this->setVariable($classForEl, $item, $codeValue, $type);
                return $this->getChildHtml('column_items', false);
            case 'Qty':
                return <<<HTML
                    <td class="$classForEl">
                        <span>$qty</span>
                    </td>
                HTML;
            case 'Price':
                $this->setImpresive(true);
                $this->setVariable($classForEl, $item, $codeValue);
                return $this->getChildHtml('column_price', false);
            case 'Subtotal':
                $this->setImpresive(true);
                $this->setVariable($classForEl, $item, $codeValue);
                return $this->getChildHtml('column_sub_total', false);
            case 'Tax':
                $order = $this->getOrder();
                $tax   = $order->formatPrice($item->getTaxAmount());
                return <<<HTML
                    <td class="$classForEl">
                        $tax
                    </td>
                HTML;
            case 'Discount':
                $order = $this->getOrder();
                $discount   = $order->formatPrice($item->getDiscountAmount());
                return <<<HTML
                    <td class="$classForEl">
                        $discount
                    </td>
                HTML;
        }
    }
}
