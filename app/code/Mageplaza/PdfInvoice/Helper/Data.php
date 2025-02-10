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
 * @category   Mageplaza
 * @package    Mageplaza_PdfInvoice
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoice\Helper;

use Magento\Catalog\Model\ProductRepository;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Email\Model\AbstractTemplate;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Item;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Information;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\PdfInvoice\Model\ResourceModel\Column\Collection;
use Mageplaza\PdfInvoice\Model\Source\PrintButton;
use Mageplaza\PdfInvoice\Model\Source\Type;
use Mageplaza\PdfInvoice\Model\TemplateFactory;
use Magento\Framework\Module\ModuleResource;

/**
 * Class Data
 * @package Mageplaza\PdfInvoice\Helper
 */
class Data extends AbstractData
{
    public const CONFIG_MODULE_PATH                 = 'pdfinvoice';
    public const BUSINESS_INFORMATION_CONFIGURATION = 'pdfinvoice/general/business_information';
    /**
     * Recipient email config path
     */
    public const XML_PATH_EMAIL_RECIPIENT = 'contact/email/recipient_email';

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Information
     */
    protected $storeInformation;

    /**
     * @var string
     */
    protected $note;

    /**
     * @var ResourceConfig
     */
    protected $resourceConfig;

    /**
     * @var Rule
     */
    protected $_rule;

    /**
     * @var QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $_invoiceRepository;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $_shipmentRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $_creditmemoRepository;

    /**
     * @var array
     */
    protected $idConditionValidated;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var DesignInterface
     */
    protected $theme;

    /**
     * @var Collection
     */
    protected $columnCollectionFactory;

    /**
     * @var ModuleResource
     */
    protected $moduleResource;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param TemplateFactory $templateFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param Information $storeInformation
     * @param ObjectManagerInterface $objectManager
     * @param ResourceConfig $resourceConfig
     * @param Rule $rule
     * @param OrderRepositoryInterface $orderRepository
     * @param QuoteFactory $quoteFactory
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param ProductRepository $productRepository
     * @param DesignInterface $theme
     */
    public function __construct(
        Context $context,
        TemplateFactory $templateFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        Information $storeInformation,
        ObjectManagerInterface $objectManager,
        ResourceConfig $resourceConfig,
        Rule $rule,
        OrderRepositoryInterface $orderRepository,
        QuoteFactory $quoteFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        ProductRepository $productRepository,
        DesignInterface $theme,
        Collection $columnCollectionFactory,
        ModuleResource $moduleResource
    ) {
        $this->templateFactory         = $templateFactory;
        $this->filesystem              = $filesystem;
        $this->storeManager            = $storeManager;
        $this->storeInformation        = $storeInformation;
        $this->resourceConfig          = $resourceConfig;
        $this->_rule                   = $rule;
        $this->_orderRepository        = $orderRepository;
        $this->_quoteFactory           = $quoteFactory;
        $this->_invoiceRepository      = $invoiceRepository;
        $this->_shipmentRepository     = $shipmentRepository;
        $this->_creditmemoRepository   = $creditmemoRepository;
        $this->_productRepository      = $productRepository;
        $this->theme                   = $theme;
        $this->columnCollectionFactory = $columnCollectionFactory;
        $this->moduleResource          = $moduleResource;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * Is enable pdf template for each type
     *
     * @param $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnableAttachment($type, $storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . $type . '/enable', $storeId);
    }

    /**
     * @param $type
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getConditionsSerialized($type, $storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . $type . '/condition', $storeId);
    }

    /**
     * Get pdf template for each type
     *
     * @param $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPdfTemplate($type, $storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . $type . '/template', $storeId);
    }

    /**
     * Can show custom print button for each type with validateConditionRule()
     *
     * @param $type
     * @param null $storeId
     * @param bool|null $isIgnoreValidateCondition
     *
     * @return bool|mixed
     */
    public function canShowCustomPrint($type, $storeId = null, bool $isIgnoreValidateCondition = null)
    {
        if (!$this->isEnabled($storeId)) {
            return false;
        }
        if ($isIgnoreValidateCondition === null || $isIgnoreValidateCondition === false) {
            $isValidated = $this->validateConditionRule($type);
            if (!$isValidated) {
                return PrintButton::DEFAULT_CORE;
            }
        }

        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . $type . '/print', $storeId);
    }

    /**
     * Get pdf invoice note
     *
     * @param $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPdfNote($type, $storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . $type . '/note', $storeId);
    }

    /**
     * Get pdf invoice file name
     *
     * @param $type
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getFileName($type, $storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . $type . '/file_name', $storeId);
    }

    /**
     * Get pdf invoice generic name
     *
     * @param $type
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getGenericName($type, $storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . $type . '/generic_name', $storeId);
    }

    /**
     * @param string $type
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function allowCustomerGroups($type, $storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . $type . '/customer_groups', $storeId);
    }

    /**
     * @param string $type
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function applyForGroups($type, $storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . $type . '/allow_groups', $storeId);
    }

    /**
     * @param string $type
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getLabel($type, $storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . $type . '/label', $storeId);
    }

    /**
     * Get business information
     *
     * @param $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getBusinessInformationConfig($code, $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigGeneral('business_information' . $code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getLogoPDF($storeId = null)
    {
        return $this->getBusinessInformationConfig('logo', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getWhiteLogoPDF($storeId = null)
    {
        return $this->getBusinessInformationConfig('white_logo', $storeId);
    }

    /**
     * Get pdf invoice design
     *
     * @param $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPdfDesign($code, $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/design' . $code, $storeId);
    }

    /**
     * Is enable page number in pdf
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnablePageNumber($storeId = null)
    {
        return $this->getPdfDesign('page_number', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPositionOfPageNum($storeId = null)
    {
        return $this->getPdfDesign('page_number_position', $storeId);
    }

    /**
     * Get page size
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPageSize($storeId = null)
    {
        return $this->getPdfDesign('page_size', $storeId);
    }

    /**
     * Set Business Information
     */
    public function setBusinessInformation()
    {
        $this->saveConfig(
            self::BUSINESS_INFORMATION_CONFIGURATION . '/company',
            $this->getConfigValue(Information::XML_PATH_STORE_INFO_NAME, 0)
        );
        $this->saveConfig(
            self::BUSINESS_INFORMATION_CONFIGURATION . '/phone',
            $this->getConfigValue(Information::XML_PATH_STORE_INFO_PHONE, 0)
        );
        $this->saveConfig(
            self::BUSINESS_INFORMATION_CONFIGURATION . '/address',
            $this->getConfigValue(Information::XML_PATH_STORE_INFO_STREET_LINE1, 0)
        );
        $this->saveConfig(
            self::BUSINESS_INFORMATION_CONFIGURATION . '/vat_number',
            $this->getConfigValue(Information::XML_PATH_STORE_INFO_VAT_NUMBER, 0)
        );
        $this->saveConfig(
            self::BUSINESS_INFORMATION_CONFIGURATION . '/contact',
            $this->getConfigValue(self::XML_PATH_EMAIL_RECIPIENT, 0)
        );

        /** @var Store $store */
        foreach ($this->storeManager->getStores() as $store) {
            $storeId   = $store->getId();
            $storeInfo = $this->storeInformation->getStoreInformationObject($store);
            $this->saveConfig(self::BUSINESS_INFORMATION_CONFIGURATION . '/company', $storeInfo->getName());
            $this->saveConfig(self::BUSINESS_INFORMATION_CONFIGURATION . '/phone', $storeInfo->getPhone());
            $this->saveConfig(
                self::BUSINESS_INFORMATION_CONFIGURATION . '/contact',
                $this->getConfigValue(self::XML_PATH_EMAIL_RECIPIENT, $storeId)
            );
            $this->saveConfig(
                self::BUSINESS_INFORMATION_CONFIGURATION . '/logo_width',
                $this->getConfigValue(AbstractTemplate::XML_PATH_DESIGN_EMAIL_LOGO_WIDTH, $storeId)
            );
            $this->saveConfig(
                self::BUSINESS_INFORMATION_CONFIGURATION . '/logo_height',
                $this->getConfigValue(AbstractTemplate::XML_PATH_DESIGN_EMAIL_LOGO_HEIGHT, $storeId)
            );
        }
    }

    /**
     * Save config
     *
     * @param string $field
     * @param string $value
     */
    public function saveConfig($field, $value)
    {
        if (!empty($value)) {
            $this->resourceConfig->saveConfig($field, $value, 'default');
        }
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getTemplatesConfig($storeId = null)
    {
        return $this->getModuleConfig('template', $storeId);
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->getTemplatesConfig() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Get Templates
     *
     * @param $type
     *
     * @return array
     */
    public function getTemplates($type)
    {
        $result            = [];
        $invoiceCollection = $this->templateFactory->create()
            ->getCollection()
            ->addFieldToFilter('type', $type);

        foreach ($invoiceCollection as $invoice) {
            $result[] = ['value' => $invoice->getId(), 'label' => $invoice->getName()];
        }
        $result = array_merge($result, $this->toOptionArray());

        return $result;
    }

    /**
     * Check template is using in config
     *
     * @param $id
     *
     * @return bool
     */
    public function checkTemplateInConfig($id)
    {
        $flag = false;
        foreach ($this->getStores() as $store) {
            $storeId    = $store->getId();
            $invoice    = $this->getPdfTemplate(Type::INVOICE, $storeId);
            $order      = $this->getPdfTemplate(Type::ORDER, $storeId);
            $shipment   = $this->getPdfTemplate(Type::SHIPMENT, $storeId);
            $creditmemo = $this->getPdfTemplate(Type::CREDIT_MEMO, $storeId);
            if ($id === $invoice || $id === $order || $id === $shipment || $id === $creditmemo) {
                $flag = true;
            }
        }

        return $flag;
    }

    /**
     * Get stores
     * @return StoreInterface[]
     */
    public function getStores()
    {
        return $this->storeManager->getStores();
    }

    /**
     * @effect Display print button on top of action list in order grid
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isTopButton($storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . 'order' . '/button_top', $storeId);
    }

    /**
     * @effect Display print invoice button in action list from order grid
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isInvoiceInOrderGrid($storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . 'invoice' . '/orderGrid_button', $storeId);
    }

    /**
     * @effect Display print shipment button in action list from order grid
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isShipmentInOrderGrid($storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . 'shipment' . '/orderGrid_button', $storeId);
    }

    /**
     * @effect Display print creditmemo button in action list from order grid
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isCreditmemoInOrderGrid($storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . 'creditmemo' . '/orderGrid_button', $storeId);
    }

    /**
     * @return bool
     */
    public function checkEmailAttachmentsIsEnable()
    {
        return $this->_moduleManager->isEnabled('Mageplaza_EmailAttachments')
            && $this->getConfigValue('mp_email_attachments/general/enabled');
    }

    /**
     * @param $type
     * @param null $orderId
     *
     * @return bool
     */
    public function validateConditionRule($type, $orderId = null)
    {
        $actions    = ['pdfinvoice_massaction_printpdf', 'mui_index_render', 'sales_order_index'];
        $invoice    = null;
        $shipment   = null;
        $creditMemo = null;
        if (!$orderId && in_array($this->_request->getFullActionName(), $actions)) {
            return true;
        }
        if (!$orderId) {
            $orderId = $this->_getRequest()->getParam('order_id');
        }
        if ($invoiceId = $this->_getRequest()->getParam('invoice_id')) {
            $invoice = $this->_invoiceRepository->get($invoiceId);
            $orderId = $invoice->getOrderId();
        }
        if ($shipmentId = $this->_getRequest()->getParam('shipment_id')) {
            $shipment = $this->_shipmentRepository->get($shipmentId);
            $orderId  = $shipment->getOrderId();
        }
        if ($creditMemoId = $this->_getRequest()->getParam('creditmemo_id')) {
            $creditMemo = $this->_creditmemoRepository->get($creditMemoId);
            $orderId    = $creditMemo->getOrderId();
        }

        if (!$orderId) {
            return true;
        }

        $order = $this->_orderRepository->get($orderId);

        return $this->validateCondition($order, $type, $invoice, $shipment, $creditMemo);
    }

    /**
     * Add Data from Order and Quote to Address
     *
     * @param Order|OrderInterface $order
     * @param string $type
     * @param Invoice|null $invoice
     * @param null $shipment
     * @param null $creditMemo
     *
     * @return bool
     */
    public function validateCondition($order, $type, $invoice = null, $shipment = null, $creditMemo = null)
    {
        $validatedId = $this->getIdValidated($order, $type, $invoice, $shipment, $creditMemo);
        if (isset($this->idConditionValidated[$validatedId])) {
            return $this->idConditionValidated[$validatedId];
        }
        $quote                    = $this->_quoteFactory->create()->load($order->getQuoteId());
        $conditionsSerialized     = $this->getConditionsSerialized($type);
        $address                  = $quote->getShippingAddress();
        $addressData              = $address->getData();
        $addressData              = array_merge($addressData, $order->getData());
        $addressData              = array_merge($addressData, $quote->getData());
        $addressData['total_qty'] = $addressData['total_qty_ordered'];
        $allItems                 = [];

        switch ($type) {
            case 'invoice':
                if ($invoice) {
                    $addressData              = array_merge($addressData, $invoice->getData());
                    $allItems                 = $this->getAllItems($invoice);
                    $addressData['total_qty'] = $this->collectTotalQty($allItems);
                    $addressData['base_subtotal_with_discount'] = $invoice->getData('base_subtotal');
                    $addressData['base_subtotal_total_incl_tax'] = $invoice->getData('subtotal_incl_tax');
                }
                break;
            case 'shipment':
                if ($shipment) {
                    $addressData              = array_merge($addressData, $shipment->getData());
                    $allItems                 = $this->getAllItems($shipment);
                    $addressData['total_qty'] = $this->collectTotalQty($allItems);
                }
                break;
            case 'creditmemo':
                if ($creditMemo) {
                    $addressData              = array_merge($addressData, $creditMemo->getData());
                    $allItems                 = $this->getAllItems($creditMemo);
                    $addressData['total_qty'] = $this->collectTotalQty($allItems);
                    $addressData['base_subtotal_with_discount'] = $creditMemo->getData('base_subtotal');
                    $addressData['base_subtotal_total_incl_tax'] = $creditMemo->getData('subtotal_incl_tax');
                }
                break;
        }
        $address->setData($addressData);
        if ($allItems) {
            $address->setData('cached_items_all', $allItems);
        }
        $rule                                     = clone $this->_rule;
        $validatedValue                           = $rule->setConditionsSerialized($conditionsSerialized)
            ->validate($address);
        $this->idConditionValidated[$validatedId] = $validatedValue;

        return $validatedValue;
    }

    /**
     * @param $object
     *
     * @return mixed
     */
    public function getAllItems($object)
    {
        $allItems = $object->getAllItems();
        foreach ($allItems as $key => $item) {
            try {
                $product = $this->_productRepository->getById($item->getProductId());
            } catch (NoSuchEntityException $e) {
                unset($allItems[$key]);
                continue;
            }
            $item->setData('product', $product);
        }

        return $allItems ?? [];
    }

    /**
     * Get Validated Id by Order and Sub Of Order.
     *
     * @param Order|OrderInterface $order
     * @param string $type
     * @param null $invoice
     * @param null $shipment
     * @param null $creditMemo
     *
     * @return string
     */
    public function getIdValidated($order, $type, $invoice = null, $shipment = null, $creditMemo = null)
    {
        $validatedId = $type . '_' . $order->getId();
        switch ($type) {
            case 'invoice':
                if ($invoice) {
                    $validatedId .= '_' . $invoice->getId();
                }
                break;
            case 'shipment':
                if ($shipment) {
                    $validatedId .= '_' . $shipment->getId();
                }
                break;
            case 'creditmemo':
                if ($creditMemo) {
                    $validatedId .= '_' . $creditMemo->getId();
                }
                break;
        }

        return $validatedId;
    }

    /**
     * @param Item[] $allItems
     *
     * @return int
     */
    public function collectTotalQty(array $allItems)
    {
        $totalQty = 0;
        foreach ($allItems as $index => $_item) {
            if (!$_item->getOrderItem()->getParentItem()) {
                $totalQty += $_item->getQty();
            }
        }

        return $totalQty;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getShowCustomPrintWithCondition($type): array
    {
        $orderId = $this->_getRequest()->getParam('order_id');
        if (!$orderId) {
            return [];
        }
        $order          = $this->_orderRepository->get($orderId);
        $creditMemoIds  = [];
        $shipmentIds    = [];
        $invoiceIds     = [];
        $printActionIds = [];
        switch ($type) {
            case 'invoice':
                if ($order->hasInvoices()) {
                    foreach ($order->getInvoiceCollection() as $invoice) {
                        if ($this->validateCondition($order, $type, $invoice)) {
                            $invoiceIds[] = $invoice->getId();
                        }
                    }
                }
                break;
            case 'shipment':
                if ($order->hasShipments()) {
                    foreach ($order->getShipmentsCollection() as $shipment) {
                        if ($this->validateCondition($order, $type, null, $shipment)) {
                            $shipmentIds[] = $shipment->getId();
                        }
                    }
                }
                break;
            case 'creditmemo':
                if ($order->hasCreditmemos()) {
                    foreach ($order->getCreditmemosCollection() as $creditMemo) {
                        if ($this->validateCondition($order, $type, null, null, $creditMemo)) {
                            $creditMemoIds[] = $creditMemo->getId();
                        }
                    }
                }
                break;
        }

        return array_merge($printActionIds, $invoiceIds, $shipmentIds, $creditMemoIds);
    }

    /**
     * @param null $inputMargin
     * @param null $storeId
     *
     * @return array
     */
    public function getPdfMargin($inputMargin = null, $storeId = null)
    {
        $marginData           = [];
        $configMargin         = $this->getPdfDesign('pdf_margin', $storeId);
        $margin               = $inputMargin !== null ? explode(';', $inputMargin) : explode(';', $configMargin);
        $marginData['top']    = $margin['0'];
        $marginData['right']  = $margin['1'];
        $marginData['bottom'] = $margin['2'];
        $marginData['left']   = $margin['3'];

        return $marginData;
    }

    /**
     * Get barcode design
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDesignBarcode($code, $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/design/barcode_setting' . $code, $storeId);
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
        $barcodeConfig = [];
        $barcodeConfig['size'] = (float) $this->getDesignBarcode('barcode_size', $storeId) < 1.6 ? $this->getDesignBarcode('barcode_size', $storeId) : '0.9';
        $barcodeConfig['type'] = $this->getDesignBarcode('barcode_type', $storeId);
        $barcodeConfig['barcode_attribute'] = $this->getDesignBarcode('barcode_attribute', $storeId);
        $barcodeConfig['qrcode_attribute'] = $this->getDesignBarcode('qrcode_attribute', $storeId);

        return $barcodeConfig;
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
        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . $type . '/code_type', $storeId);
    }

    /**
     * @return bool
     */
    public function isEnabledHyvaTheme()
    {
        if (str_contains($this->theme->getDesignTheme()->getCode(), "Hyva")) {
            return true;
        };

        return false;
    }

    /**
     * Get columns config
     *
     * @param $type
     * @return array|array[]
     */
    public function getColumns($type)
    {
        $templateID = $this->_getRequest()->getParam('id');
        $fullActionName = $this->_getRequest()->getFullActionName();
        if (!$templateID && $fullActionName === 'pdfinvoice_template_edit') {
            return $this->getColumnsForDefault($type);
        }
        if (!$templateID) {
            $templateID = $this->getPdfTemplate($type);
        }
        return $this->getColumnsById($templateID, $type);
    }

    /**
     * Get columns config by template id
     *
     * @param $id
     * @param $type
     * @return array|array[]
     */
    public function getColumnsById($id, $type)
    {
        $columnCollection = $this->columnCollectionFactory->addFieldToFilter('template_id', $id)->setOrder('position', 'ASC');
        if ($columnCollection->getSize() === 0) {
            return $this->getColumnsForDefault($type);
        }
        $columns = [];
        foreach ($columnCollection as $column) {
            $columns[] = [
                'id'       => $column->getColumnId(),
                'status'   => $column->getStatus(),
                'name'     => $column->getName(),
                'position' => $column->getPosition(),
            ];
        }
        return $columns;
    }

    /**
     * Get columns config for default template
     *
     * @param $type
     * @return array|array[]
     */
    public function getColumnsForDefault($type)
    {
        if ($type === 'shipment') {
            $columns = [
                [
                    'id'       => null,
                    'status'   => 1,
                    'name'     => 'Items',
                    'position' => 1
                ], [
                    'id'       => null,
                    'status'   => 1,
                    'name'     => 'Qty',
                    'position' => 2
                ], [
                    'id'       => null,
                    'status'   => 1,
                    'name'     => 'Price',
                    'position' => 3
                ]
            ] ;
        } else {
            $columns = [
                [
                    'id'       => null,
                    'status'   => 1,
                    'name'     => 'Items',
                    'position' => 1
                ], [
                    'id'       => null,
                    'status'   => 1,
                    'name'     => 'Qty',
                    'position' => 2
                ], [
                    'id'       => null,
                    'status'   => 1,
                    'name'     => 'Price',
                    'position' => 3
                ], [
                    'id'       => null,
                    'status'   => 1,
                    'name'     => 'Subtotal',
                    'position' => 4
                ], [
                    'id'       => null,
                    'status'   => 0,
                    'name'     => 'Tax',
                    'position' => 5
                ], [
                    'id'       => null,
                    'status'   => 0,
                    'name'     => 'Discount',
                    'position' => 6
                ]

            ] ;
        }
        return $columns;
    }

    /**
     * Check warning display conditions
     *
     * @param $templateID
     * @return bool
     */

    public function displayWarningText($templateID)
    {
        $moduleVersion =  $this->moduleResource->getDataVersion('Mageplaza_PdfInvoice');
        if (version_compare($moduleVersion, '4.5.2', '>')) {
            $template = $this->templateFactory->create()
                ->getCollection()
                ->addFieldToFilter('template_id', $templateID)->getFirstItem();
            if (!$template->getData('is_new_template')) {
                return true;
            }
        }
        return false;
    }
}
