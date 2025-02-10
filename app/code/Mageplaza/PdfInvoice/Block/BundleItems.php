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

use Magento\Backend\Block\Template\Context;
use Magento\Bundle\Block\Adminhtml\Sales\Order\Items\Renderer;
use Magento\Catalog\Helper\Image;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Registry;
use Magento\GiftMessage\Helper\Message;
use Magento\Tax\Helper\Data;
use Mageplaza\PdfInvoice\Model\Source\Type;

/**
 * Class BundleItems
 * @package Mageplaza\PdfInvoice\Block
 */
class BundleItems extends Renderer
{
    /**
     * @var Data
     */
    protected $taxHelper;

    /**
     * @var $storeId
     */
    protected $storeId;

    /**
     * @var Image
     */
    protected $helperImage;

    /**
     * @var Message
     */
    protected $helperMessage;

    /**
     * BundleItems constructor.
     *
     * @param Context $context
     * @param StockRegistryInterface $stockRegistry
     * @param StockConfigurationInterface $stockConfiguration
     * @param Registry $registry
     * @param Image $helperImage
     * @param Message $helperMessage
     * @param Data $taxHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration,
        Registry $registry,
        Image $helperImage,
        Message $helperMessage,
        Data $taxHelper,
        array $data = []
    ) {
        $this->taxHelper = $taxHelper;
        $this->stockRegistry = $stockRegistry;
        $this->stockConfiguration = $stockConfiguration;
        $this->_coreRegistry = $registry;
        $this->helperImage = $helperImage;
        $this->helperMessage = $helperMessage;

        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
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
     * Set store id
     *
     * @param $id
     */
    public function setStoreId($id)
    {
        $this->storeId = $id;
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
     * @param $type
     *
     * @return bool
     */
    public function isTypeOrder($type)
    {
        return $type == Type::ORDER;
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
     * @param $key
     * @param $a4Barcode
     * @param $item
     * @param $codeValue
     * @return string|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTdBundleHtml($key, $a4Barcode, $isBarcode, $item, $codeValue, $type = 'invoice', $forA5 = false)
    {
        $classForEl = $a4Barcode ? 'mp-'.strtolower($key).'-bc' : 'mp-'.strtolower($key);
        if ($key === 'Items') $classForEl = $a4Barcode ? 'mp-item-bc' : 'mp-item';
        $qty        = $item->getQty() * 1;
        if ($type === 'order' || $type === 'shipment') {
            $qty = $item->getQtyOrdered() * 1;
        }
        $hasParent = $item->getParentItem() || ($item->getOrderItem() && $item->getOrderItem()->getParentItem());
        switch ($key) {
            case 'Items':
                if ($forA5) {
                    return $this->getLayout()
                        ->createBlock('Mageplaza\PdfInvoice\Block\BundleItems')
                        ->setData(['item' => $item, 'classForEl'=>$classForEl, 'key'=>$key, 'isBarcode'=>$isBarcode, 'codeType'=>$this->getData('code_type'), 'pageSize'=>$this->getData('page_size'), 'codeValue'=>$codeValue, 'barcodeConfig'=>$this->getData('barcode_config'), 'hasParent'=>$hasParent, 'type'=>$type])
                        ->setTemplate('Mageplaza_PdfInvoice::handle/column/itemsA5Bundle.phtml')
                        ->toHtml();
                } else {
                    return $this->getLayout()
                        ->createBlock('Mageplaza\PdfInvoice\Block\BundleItems')
                        ->setData(['item' => $item, 'classForEl'=>$classForEl, 'key'=>$key, 'isBarcode'=>$isBarcode, 'codeType'=>$this->getData('code_type'), 'pageSize'=>$this->getData('page_size'), 'codeValue'=>$codeValue, 'barcodeConfig'=>$this->getData('barcode_config'), 'hasParent'=>$hasParent, 'type'=>$type])
                        ->setTemplate('Mageplaza_PdfInvoice::handle/column/itemsBundle.phtml')
                        ->toHtml();
                }
            case 'Qty':
                if ($hasParent) {
                    return <<<HTML
                    <div class="$classForEl">
                        <span>$qty</span>
                    </div>
                HTML;
                } else {
                    return '';
                }
            case 'Price':
                return $this->getLayout()
                    ->createBlock('Mageplaza\PdfInvoice\Block\BundleItems')
                    ->setData(['item' => $item, 'classForEl'=>$classForEl, 'key'=>$key, 'hasParent'=>$hasParent])
                    ->setTemplate('Mageplaza_PdfInvoice::handle/column/priceBundle.phtml')
                    ->toHtml();
            case 'Subtotal':
                return $this->getLayout()
                    ->createBlock('Mageplaza\PdfInvoice\Block\BundleItems')
                    ->setData(['item' => $item, 'classForEl'=>$classForEl, 'key'=>$key, 'hasParent'=>$hasParent])
                    ->setTemplate('Mageplaza_PdfInvoice::handle/column/subtotalBundle.phtml')
                    ->toHtml();
            case 'Tax':
                if ($hasParent) {
                    $order = $this->getOrder();
                    $tax   = $order->formatPrice($item->getTaxAmount());
                    return <<<HTML
                        <div class="$classForEl">
                            $tax
                        </div>
                    HTML;
                } else {
                    return '';
                }
            case 'Discount':
                if ($hasParent) {
                    $order = $this->getOrder();
                    $discount   = $order->formatPrice($item->getDiscountAmount());
                    return <<<HTML
                        <div class="$classForEl">
                            $discount
                        </div>
                    HTML;
                } else {
                    return '';
                }
        }
    }

    /**
     * Get value for table
     *
     * @param $key
     * @param $a4Barcode
     * @param $item
     * @param $codeValue
     * @param $type
     * @return string|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTdImpresiveHtml($key, $a4Barcode, $item, $codeValue, $type)
    {
        $classForEl = $a4Barcode ? 'mp-'.strtolower($key).'-bc' : 'mp-'.strtolower($key);
        if ($key === 'Items') $classForEl = $a4Barcode ? 'mp-item-bc' : 'mp-item';
        $qty        = $item->getQty() * 1;
        if ($type === 'order' || $type === 'shipment') {
            $qty = $item->getQtyOrdered() * 1;
        }
        $hasParent = $item->getParentItem() || ($item->getOrderItem() && $item->getOrderItem()->getParentItem());
        switch ($key) {
            case 'Items':
                return $this->getLayout()
                    ->createBlock('Mageplaza\PdfInvoice\Block\BundleItems')
                    ->setData(['item' => $item, 'classForEl'=>$classForEl, 'key'=>$key, 'isBarcode'=>$a4Barcode, 'codeType'=>$this->getData('code_type'), 'pageSize'=>$this->getData('page_size'), 'codeValue'=>$codeValue, 'barcodeConfig'=>$this->getData('barcode_config'), 'isImpresive'=>true, 'hasParent'=>$hasParent, 'type'=>$type])
                    ->setTemplate('Mageplaza_PdfInvoice::handle/column/itemsBundle.phtml')
                    ->toHtml();
            case 'Qty':
                if ($hasParent) {
                    return <<<HTML
                    <td class="$classForEl">
                        <span>$qty</span>
                    </td>
                    HTML;
                } else {
                    return '';
                }
            case 'Price':
                $this->setImpresive(true);
                return $this->getLayout()
                    ->createBlock('Mageplaza\PdfInvoice\Block\BundleItems')
                    ->setData(['item' => $item, 'classForEl'=>$classForEl, 'key'=>$key, 'isImpresive'=>true, 'hasParent'=>$hasParent])
                    ->setTemplate('Mageplaza_PdfInvoice::handle/column/priceBundle.phtml')
                    ->toHtml();
            case 'Subtotal':
                $this->setImpresive(true);
                return $this->getLayout()
                    ->createBlock('Mageplaza\PdfInvoice\Block\BundleItems')
                    ->setData(['item' => $item, 'classForEl'=>$classForEl, 'key'=>$key, 'isImpresive'=>true, 'hasParent'=>$hasParent])
                    ->setTemplate('Mageplaza_PdfInvoice::handle/column/subtotalBundle.phtml')
                    ->toHtml();
            case 'Tax':
                if ($hasParent) {
                    $order = $this->getOrder();
                    $tax   = $order->formatPrice($item->getTaxAmount());
                    return <<<HTML
                        <td class="$classForEl">
                            $tax
                        </td>
                    HTML;
                } else {
                    return '';
                }
            case 'Discount':
                if ($hasParent) {
                    $order = $this->getOrder();
                    $discount   = $order->formatPrice($item->getDiscountAmount());
                    return <<<HTML
                        <td class="$classForEl">
                            $discount
                        </td>
                    HTML;
                } else {
                    return '';
                }
        }
    }
}
