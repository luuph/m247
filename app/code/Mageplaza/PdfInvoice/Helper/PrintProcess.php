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

use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\PdfInvoice\Helper\Data as HelperData;
use Mageplaza\PdfInvoice\Model\CustomFunction;
use Mageplaza\PdfInvoice\Model\Source\Type;
use Mageplaza\PdfInvoice\Model\Template\Processor;
use Mageplaza\PdfInvoice\Model\TemplateFactory;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

/**
 * Class PrintProcess
 * @package Mageplaza\PdfInvoice\Helper
 */
class PrintProcess extends AbstractData
{
    public const MAGEPLAZA_DIR = 'var/mageplaza';

    /**
     * Module registry
     *
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var string
     */
    protected $templateStyles = '';

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var $templateVars
     */
    protected $templateVars;

    /**
     * @var Processor
     */
    protected $templateProcessor;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var string
     */
    protected $fileName = 'PdfInvoice';

    /**
     * @var State
     */
    protected $state;

    /**
     * @var CustomFunction
     */
    protected $customFunction;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * @var Creditmemo
     */
    protected $creditmemo;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Invoice
     */
    protected $invoice;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var Shipment
     */
    protected $shipment;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CountryFactory
     */
    private $_countryFactory;
    /**
     * @var Repository
     */
    protected $_assetRepo;

    /**
     * PrintProcess constructor.
     *
     * @param Context $context
     * @param Order $order
     * @param State $state
     * @param Invoice $invoice
     * @param Shipment $shipment
     * @param Data $helperData
     * @param Filesystem $fileSystem
     * @param Creditmemo $creditmemo
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param Processor $templateProcessor
     * @param DirectoryList $directoryList
     * @param CustomFunction $customFunction
     * @param TemplateFactory $templateFactory
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param CustomerFactory $customerFactory
     * @param CountryFactory $countryFactory
     * @param Repository $assetRepo
     */
    public function __construct(
        Context $context,
        Order $order,
        State $state,
        Invoice $invoice,
        Shipment $shipment,
        HelperData $helperData,
        Filesystem $fileSystem,
        Creditmemo $creditmemo,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        Processor $templateProcessor,
        DirectoryList $directoryList,
        CustomFunction $customFunction,
        TemplateFactory $templateFactory,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        ComponentRegistrarInterface $componentRegistrar,
        ShipmentRepositoryInterface $shipmentRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        CustomerFactory $customerFactory,
        CountryFactory $countryFactory,
        Repository $assetRepo
    ) {
        $this->order                = $order;
        $this->state                = $state;
        $this->invoice              = $invoice;
        $this->shipment             = $shipment;
        $this->creditmemo           = $creditmemo;
        $this->helperData           = $helperData;
        $this->fileSystem           = $fileSystem;
        $this->paymentHelper        = $paymentHelper;
        $this->directoryList        = $directoryList;
        $this->customFunction       = $customFunction;
        $this->addressRenderer      = $addressRenderer;
        $this->templateFactory      = $templateFactory;
        $this->orderRepository      = $orderRepository;
        $this->invoiceRepository    = $invoiceRepository;
        $this->templateProcessor    = $templateProcessor;
        $this->componentRegistrar   = $componentRegistrar;
        $this->shipmentRepository   = $shipmentRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->customerFactory      = $customerFactory;
        $this->_countryFactory      = $countryFactory;
        $this->_assetRepo           = $assetRepo;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * Check default template
     *
     * @param string|int $templateId
     *
     * @return bool
     */
    public function checkDefaultTemplate($templateId)
    {
        return array_key_exists($templateId, $this->helperData->getTemplatesConfig());
    }

    /**
     * @param string $templateType
     * @param string|int $templateId
     *
     * @return string
     * @throws FileSystemException
     */
    public function getDefaultTemplateHtml($templateType, $templateId)
    {
        return $this->readFile($this->getTemplatePath($templateType, $templateId));
    }

    /**
     * @param string $templateType
     * @param string|int $templateId
     *
     * @return string
     * @throws FileSystemException
     */
    public function getDefaultTemplateCss($templateType, $templateId)
    {
        return $this->readFile($this->getTemplatePath($templateType, $templateId, '.css'));
    }

    /**
     * Get default template path
     *
     * @param string $templateType
     * @param string|int $templateId
     * @param string $type
     *
     * @return string
     */
    public function getTemplatePath($templateType, $templateId, $type = '.html')
    {
        return $this->getBaseTemplatePath() . $templateType . '/' . $templateId . $type;
    }

    /**
     * @param string $relativePath
     *
     * @return string
     * @throws FileSystemException
     */
    public function readFile($relativePath)
    {
        $rootDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::ROOT);

        return $rootDirectory->readFile($relativePath);
    }

    /**
     * @param string|int $templateId
     * @param string $type
     *
     * @return string
     * @throws FileSystemException
     */
    public function getTemplateHtml($templateId, $type)
    {
        if ($this->checkDefaultTemplate($templateId)) {
            $this->templateStyles = $this->getDefaultTemplateCss($type, $templateId);

            return $this->getDefaultTemplateHtml($type, $templateId);
        }

        $templateModel = $this->templateFactory->create();
        $template      = $templateModel->load($templateId);
        if ($template->getId()) {
            $this->templateStyles = $template->getTemplateStyles();

            return $template->getTemplateHtml();
        }
    }

    /**
     * Process pdf template for each type
     *
     * @param string $type
     * @param array $templateVars
     * @param int $storeId
     * @param Order $object
     *
     * @return string
     * @throws FileSystemException
     * @throws MpdfException
     */
    public function processPDFTemplate($type, $templateVars, $storeId, $object)
    {
        $orderId = $object->getId() ?: $object->getOrderId();
        if ($this->helperData->isEnableAttachment($type, $storeId)
            && $this->isAllowCustomerGroup($type, $object, $storeId)
            && $this->validateCondition($type, $orderId)
        ) {
            $templateId                    = $this->helperData->getPdfTemplate($type, $storeId);
            $templateHtml                  = $this->getTemplateHtml($templateId, $type);
            $templateVars[$type . 'Note']  = $this->helperData->getPdfNote($type, $storeId);
            $order                         = $templateVars['order'];
            $imgHeaderUrl                  = $this->getHeaderImgUrl();
            $templateVars['header4']       = $imgHeaderUrl->getData('header4');
            $templateVars['header5']       = $imgHeaderUrl->getData('header5');
            $templateVars['customer_name'] = $order->getCustomerName();
            $templateVars['payment_html']  = $this->getPaymentHtml($order, $storeId);
            $templateVars['order_id']      = $order->getId();
            if ($type !== 'order') {
                $templateVars[$type . '_id'] = $templateVars[$type]->getId();
            }
            $templateVars[$type . '_date'] = $this->customFunction->formatDate($templateVars[$type]->getData('created_at'));
            if ($type === 'shipment') {
                $templateVars['shipment_amount'] = $order->formatPriceTxt($order->getShippingAmount());
            } else {
                $templateVars[$type . '_amount'] = $order->formatPriceTxt($templateVars[$type]->getGrandTotal());
            }

            return $this->getPDFContent($templateHtml, $templateVars, 'S', $storeId);
        }

        return '';
    }

    /**
     * Set template vars
     *
     * @param array $data
     *
     * @return mixed
     */
    public function setTemplateVars($data)
    {
        return $this->templateVars = $data;
    }

    /**
     * Get store id
     *
     * @return mixed
     */
    public function getStoreId()
    {
        $store = $this->templateVars['store'];

        return $store->getId();
    }

    /**
     * Get PDF Content
     *
     * @param string $html
     * @param array $templateVars
     * @param string $dest
     * @param null $storeId
     *
     * @return string
     * @throws MpdfException
     */
    public function getPDFContent($html, $templateVars, $dest = 'S', $storeId = null)
    {
        $processor = $this->templateProcessor->setVariable(
            $this->addCustomTemplateVars($templateVars, $storeId)
        );
        $processor->setTemplateHtml($html . '<style>' . $this->templateStyles . '</style>');
        $processor->setStore($storeId);

        $html = $processor->processTemplate();
        if ($this->getMode() === 'prints') {
            return $html;
        }

        return $this->exportToPDF($this->fileName . '.pdf', $html, $storeId, $dest);
    }

    /**
     * Set mode print
     *
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Get mode print
     *
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Print all pdf of  all store view
     *
     * @param string $type
     * @param array $ids
     *
     * @throws Exception
     * @throws LocalizedException
     * @throws MpdfException
     */
    public function printAllPdf($type, $ids)
    {
        if (is_array($ids)) {
            if (count($ids) === 1) {
                /** Get the first key and item of $ids*/
                $item = reset($ids);
                $sid  = key($ids);

                /** When all of selected items are belong to only one store, export the pdf file*/
                if (strpos($this->helperData->getGenericName($type), '%date') !== false) {
                    $fileNamePdf = str_replace(
                        "%date",
                        date('Y-m-d'),
                        $this->helperData->getGenericName($type)
                    ) . '.pdf';
                } else {
                    if (!is_null($this->helperData->getGenericName($type))) {
                        $fileNamePdf = $this->helperData->getGenericName($type) . '.pdf';
                    } else {
                        $fileNamePdf = $type . 's' . date('Y-m-d') . '.pdf';
                    }
                }

                $this->getMpdfContent($sid, $item, $type)->Output($fileNamePdf, 'D');
            } else {
                /** Create directory var/mageplaza/pdfinvoice and var/mageplaza/tmp if not exists*/
                if (!file_exists(self::MAGEPLAZA_DIR)) {
                    mkdir(self::MAGEPLAZA_DIR);
                }
                if (!file_exists(self::MAGEPLAZA_DIR . DIRECTORY_SEPARATOR . 'pdfinvoice')) {
                    mkdir(self::MAGEPLAZA_DIR . DIRECTORY_SEPARATOR . 'pdfinvoice');
                }
                if (!file_exists(self::MAGEPLAZA_DIR . DIRECTORY_SEPARATOR . 'tmp')) {
                    mkdir(self::MAGEPLAZA_DIR . DIRECTORY_SEPARATOR . 'tmp');
                }

                foreach ($ids as $sid => $item) {
                    /** Each store export one pdf file*/
                    if (strpos($this->helperData->getGenericName($type), '%date') !== false) {
                        $fileNamePdf = str_replace(
                            "%date",
                            date('Y-m-d'),
                            $this->helperData->getGenericName($type)
                        ) . '.pdf';
                    } else {
                        if (!is_null($this->helperData->getGenericName($type))) {
                            $fileNamePdf = $this->helperData->getGenericName($type) . '.pdf';
                        } else {
                            $fileNamePdf = $type . 's' . date('Y-m-d') . '.pdf';
                        }
                    }
                    $this->getMpdfContent($sid, $item, $type)->Output(
                        self::MAGEPLAZA_DIR .
                        DIRECTORY_SEPARATOR .
                        'pdfinvoice' .
                        DIRECTORY_SEPARATOR .
                        $sid .
                        '-' .
                        $fileNamePdf,
                        'F'
                    );
                }
                /** Zip all of exported pdf files and download*/
                $this->downloadFile($this->packFile());
            }
        }
    }

    /**
     * Get Mpdf content
     *
     * @param int $sid
     * @param array $item
     * @param string $type
     *
     * @return Mpdf
     * @throws Exception
     * @throws LocalizedException
     * @throws MpdfException
     */
    public function getMpdfContent($sid, $item, $type)
    {
        $pageSize = $this->helperData->getPageSize($sid);
        $mpdf     = $this->createMpdf($pageSize);
        $this->setPageNumber($mpdf);
        $this->setMode('prints');
        foreach ($item as $id) {
            $html = $this->printPdf($type, $id);
            $mpdf->WriteHTML($html);
            if ($id !== end($item)) {
                $mpdf->addPage();
            }
        }

        return $mpdf;
    }

    /***
     * Export zip file to Frontend
     *
     * @param string $zipFile
     */
    public function downloadFile($zipFile)
    {
        if (file_exists($zipFile)) {
            header("Content-type: application/zip");
            header("Content-Disposition: attachment; filename=file.zip");
            header("Pragma: no-cache");
            header("Expires: 0");
            readfile($zipFile);
        }
    }

    /***
     * Pack to zip file and delete all file .pdf
     *
     * @return string
     */
    public function packFile()
    {
        // Get real path for our folder
        $rootPath = realpath(self::MAGEPLAZA_DIR . DIRECTORY_SEPARATOR . 'pdfinvoice');
        $zipPath  = realpath(self::MAGEPLAZA_DIR . DIRECTORY_SEPARATOR . 'tmp');

        // Initialize archive object
        $zip     = new ZipArchive();
        $zipFile = $zipPath . DIRECTORY_SEPARATOR . 'file.zip';
        $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $filesToDelete = [];

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath     = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                $zip->addFile($filePath, $relativePath);
                $filesToDelete[] = $filePath;
            }
        }

        $zip->close();

        // Delete all files from var/Mageplaza/PdfInvoice
        foreach ($filesToDelete as $file) {
            unlink($file);
        }

        return $zipFile;
    }

    /**
     * @param int $storeId
     *
     * @return int
     * @throws LocalizedException
     */
    public function checkStoreId($storeId)
    {
        if ($this->state->getAreaCode() === Area::AREA_FRONTEND) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        return $storeId;
    }

    /**
     * get invoice ids
     *
     * @param int $orderId
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getInvoiceIds($orderId)
    {
        $order = $this->order->load($orderId);
        $ids   = [];
        foreach ($order->getInvoiceCollection() as $invoice) {
            $currentStoreId         = $this->storeManager->getStore()->getId();
            $ids[$currentStoreId][] = $invoice->getId();
        }

        return $ids;
    }

    /**
     * Get Shipment ids
     *
     * @param int $orderId
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getShipmentIds($orderId)
    {
        $order = $this->order->load($orderId);
        $ids   = [];
        foreach ($order->getShipmentsCollection() as $shipment) {
            $currentStoreId         = $this->storeManager->getStore()->getId();
            $ids[$currentStoreId][] = $shipment->getId();
        }

        return $ids;
    }

    /**
     * Get credit memo ids
     *
     * @param int $orderId
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCreditmemoIds($orderId)
    {
        $order = $this->order->load($orderId);
        $ids   = [];
        foreach ($order->getCreditmemosCollection() as $creditmemo) {
            $currentStoreId         = $this->storeManager->getStore()->getId();
            $ids[$currentStoreId][] = $creditmemo->getId();
        }

        return $ids;
    }

    /**
     * @param string $fileName
     * @param string $html
     * @param int $storeId
     * @param string $dest
     *
     * @return string
     * @throws MpdfException
     */
    public function exportToPDF($fileName, $html, $storeId, $dest)
    {
        $pageSize = $this->helperData->getPageSize($storeId) ?: 'A4';
        $mpdf     = $this->createMpdf($pageSize, $storeId);
        $this->setPageNumber($mpdf);
        $mpdf->WriteHTML($html);

        return $mpdf->Output($fileName, $dest);
    }

    /**
     * set Header or Footer
     *
     * @param Mpdf $mpdf
     * @param $storeId
     */
    public function setPageNumber($mpdf, $storeId = null)
    {
        $mpdf->SetAutoPageBreak(true, 5);
        if ($this->helperData->isEnablePageNumber($storeId)) {
            $page = 'Page {PAGENO} of {nb}&nbsp;&nbsp;&nbsp;&nbsp;';
            if ($this->helperData->getPositionOfPageNum() === 'bottom') {
                $mpdf->setFooter($page);
            } else {
                $mpdf->SetHeader($page);
            }
        }
    }

    /**
     * Create mpdf
     *
     * @param string $pageSize
     * @param null $storeId
     *
     * @return Mpdf
     * @throws MpdfException
     */
    public function createMpdf($pageSize, $storeId = null)
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs      = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData          = $defaultFontConfig['fontdata'];
        $pdfMargin         = $this->helperData->getPdfMargin(null, $storeId);
        unset($fontData['dejavusanscondensed']);
        $config = [
            'mode'              => 'utf-8',
            'format'            => $pageSize,
            'default_font_size' => 0,
            'margin_left'       => $pdfMargin['left'],
            'margin_right'      => $pdfMargin['right'],
            'margin_top'        => ($this->helperData->getPositionOfPageNum($storeId) === 'top' && (int) $pdfMargin['top'] < 10) ? 10 : $pdfMargin['top'],
            'margin_bottom'     => $pdfMargin['bottom'],
            'margin_header'     => 0,
            'margin_footer'     => 0,
            'fontDir'           => array_merge($fontDirs, [$this->getFontDirectory()]),
            'fontdata'          => $fontData + [
                    'roboto'              => [
                        'R'  => 'roboto/Roboto-Regular.ttf',
                        'B'  => 'roboto/Roboto-Bold.ttf',
                        'I'  => 'roboto/Roboto-Italic.ttf',
                        'BI' => 'roboto/Roboto-BoldCondensedItalic.ttf',
                    ],
                    'lato'                => [
                        'R'  => 'lato/Lato-Regular.ttf',
                        'B'  => 'lato/Lato-Bold.ttf',
                        'I'  => 'lato/Lato-Italic.ttf',
                        'BI' => 'lato/Lato-BoldItalic.ttf',
                    ],
                    'roboto_condensed'    => [
                        'R'  => 'roboto_condensed/RobotoCondensed-Regular.ttf',
                        'B'  => 'roboto_condensed/RobotoCondensed-Bold.ttf',
                        'I'  => 'roboto_condensed/RobotoCondensed-Italic.ttf',
                        'BI' => 'roboto_condensed/RobotoCondensed-BoldItalic.ttf',
                        'L'  => 'roboto_condensed/RobotoCondensed-Light.ttf',
                        'LI' => 'roboto_condensed/RobotoCondensed-LightItalic.ttf',
                    ],
                    'dejavusanscondensed' => [
                        'R'          => 'dejavu/DejaVuSansCondensed.ttf',
                        'B'          => 'dejavu/DejaVuSansCondensed-Bold.ttf',
                        'I'          => 'dejavu/DejaVuSansCondensed-Oblique.ttf',
                        'BI'         => 'dejavu/DejaVuSansCondensed-BoldOblique.ttf',
                        'useOTL'     => 255,
                        'useKashida' => 75,
                    ],
                    'opens_san'           => [
                        'R'  => 'opens_san/OpenSans-Regular.ttf',
                        'B'  => 'opens_san/OpenSans-Bold.ttf',
                        'I'  => 'opens_san/OpenSans-Italic.ttf',
                        'BI' => 'opens_san/OpenSans-BoldItalic.ttf',
                    ],
                    'oswald'              => [
                        'R' => 'oswald/Oswald-Regular.ttf',
                        'B' => 'oswald/Oswald-Bold.ttf',
                        'L' => 'oswald/Oswald-Light.ttf',
                    ],
                    'montserrat'          => [
                        'R'  => 'montserrat/Montserrat-Regular.ttf',
                        'B'  => 'montserrat/Montserrat-Bold.ttf',
                        'I'  => 'montserrat/Montserrat-Italic.ttf',
                        'BI' => 'montserrat/Montserrat-BoldItalic.ttf',
                    ],
                    'fontawesome'         => [
                        'R' => 'fontawesome/fontawesome-webfont.ttf',
                    ],
                ],
            'default_font'      => 'roboto',
            'orientation'       => 'P',
            'tempDir'           => BP . '/var/tmp',
            'autoScriptToLang'  => true,
            'baseScript'        => 1,
            'autoVietnamese'    => true,
            'autoArabic'        => true,
            'autoLangToFont'    => true,
        ];

        return new Mpdf($config);
    }

    /**
     * Add custom template vars
     *
     * @param array $templateVars
     * @param int $storeId
     *
     * @return mixed
     */
    public function addCustomTemplateVars($templateVars, $storeId)
    {
        if (!empty($this->getLogoUrl($storeId))) {
            $templateVars['logo_url'] = $this->getLogoUrl($storeId);
        }

        $templateVars['logo_white_url']      = $this->getLogoUrl($storeId, 'white');
        $templateVars['businessInformation'] = $this->getBusinessInformation($storeId);
        $templateVars['pdfInvoiceDesign']    = new DataObject($this->helperData->getPdfDesign('', $storeId));
        $templateVars['pdfInvoiceCustom']    = $this->customFunction;

        return $templateVars;
    }

    /**
     * Return payment info block as html
     *
     * @param Order $order
     * @param int $storeId
     *
     * @return string
     * @throws Exception
     */
    protected function getPaymentHtml(Order $order, $storeId)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $storeId
        );
    }

    /**
     * Get format shipping address
     *
     * @param Order $order
     *
     * @return string|null
     */
    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * Get format Billing address
     *
     * @param Order $order
     *
     * @return string|null
     */
    protected function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }

    /**
     * Get data preview
     *
     * @param string $type
     * @param null $id
     *
     * @return CreditmemoInterface|InvoiceInterface|OrderInterface|ShipmentInterface
     */
    public function getDataOrder($type = Type::INVOICE, $id = null)
    {
        switch ($type) {
            case Type::CREDIT_MEMO:
                if (empty($id)) {
                    $id = $this->creditmemo->getCollection()->getFirstItem()->getId();
                }

                $this->setComment($this->creditmemo->load($id));
                $model = $this->creditmemoRepository->get($id);

                break;
            case Type::ORDER:
                if (empty($id)) {
                    $id = $this->order->getCollection()->getFirstItem()->getId();
                }
                $model = $this->orderRepository->get($id);
                break;
            case Type::SHIPMENT:
                if (empty($id)) {
                    $id = $this->shipment->getCollection()->getFirstItem()->getId();
                }
                $this->setComment($this->shipment->load($id));
                $model = $this->shipmentRepository->get($id);
                break;
            default:
                if (empty($id)) {
                    $id = $this->invoice->getCollection()->getFirstItem()->getId();
                }

                $this->setComment($this->invoice->load($id));
                $model = $this->invoiceRepository->get($id);
        }

        if (strpos($this->helperData->getFileName($type, $model->getStoreId()), '%increment_id') !== false) {
            $this->fileName = str_replace(
                "%increment_id",
                $model->getIncrementId(),
                $this->helperData->getFileName($type, $model->getStoreId())
            );
        } else {
            if (!is_null($this->helperData->getFileName($type, $model->getStoreId()))) {
                $this->fileName = $this->helperData->getFileName($type, $model->getStoreId());
            } else {
                $this->fileName = $type . $model->getIncrementId();
            }
        }

        return $model;
    }

    /**
     * Set comment
     *
     * @param Invoice|Shipment|Creditmemo $model
     */
    public function setComment($model)
    {
        $this->comment = $model->getCustomerNoteNotify() ? $model->getCustomerNote() : '';
    }

    /**
     * Get Comment
     *
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param null $storeId
     * @param string $type
     *
     * @return string
     */
    public function getLogoUrl($storeId = null, $type = 'black')
    {
        $logoUrl = '';
        $logoPdf = $type === 'white'
            ? $this->helperData->getWhiteLogoPDF($storeId)
            : $this->helperData->getLogoPDF($storeId);
        if (!empty($logoPdf)) {
            $logoUrl = $this->_urlBuilder->getBaseUrl(['_type' => 'media']) . 'mageplaza/pdfinvoice/' . $logoPdf;
        }

        return $logoUrl;
    }

    /**
     * Get business information
     *
     * @param int $storeId
     *
     * @return DataObject|mixed
     */
    public function getBusinessInformation($storeId)
    {
        $data = [];
        if (is_array($this->helperData->getBusinessInformationConfig('', $storeId))) {
            $data = $this->helperData->getBusinessInformationConfig('', $storeId);
        }

        if ($data['logo_width'] === null) {
            $data['logo_width'] = 180;
        }

        if ($data['logo_height'] === null) {
            $data['logo_height'] = 30;
        }

        return new DataObject($data);
    }

    /**
     * @param string $type
     * @param null $id
     *
     * @return mixed
     * @throws Exception
     * @throws LocalizedException
     */
    public function getDataProcess($type, $id = null)
    {
        $dataOrder = $this->getDataOrder($type, $id);
        $typeTitle = $type;
        if ($type === 'order') {
            /** @var Order $order */
            $order = $dataOrder;
            $data  = ['order' => $dataOrder];
        } else {
            /** @var Order $order */
            $order = $dataOrder->getOrder();
            $data  = [
                'order'       => $order,
                'comment'     => $this->getComment(),
                $type         => $dataOrder,
                $type . '_id' => $dataOrder->getId(),
            ];
        }
        $billingAddress                      = $order->getBillingAddress();
        $shippingAddress                     = $order->getShippingAddress();
        $storeId                             = $this->checkStoreId($order->getStore()->getId());
        $imgHeaderUrl                        = $this->getHeaderImgUrl();
        $data['order_id']                    = $order->getId();
        $data['payment_html']                = $this->getPaymentHtml($order, $storeId);
        $data['payment_title']               = $order->getPayment()->getMethodInstance()->getTitle();
        $data['store']                       = $order->getStore();
        $data['formattedShippingAddress']    = $this->getFormattedShippingAddress($order);
        $data['shippingAddress']             = $shippingAddress;
        $data['shippingAddress_getStreet']   = $this->getStreet($shippingAddress);
        $data['shippingAddress_getFullName'] = $this->getFullName($shippingAddress);
        $data['countryShipping']             = $this->getCountryById($shippingAddress);
        $data['formattedBillingAddress']     = $this->getFormattedBillingAddress($order);
        $data['billingAddress']              = $order->getBillingAddress();
        $data['billingAddress_getStreet']    = $this->getStreet($billingAddress);
        $data['billingAddress_getFullName']  = $this->getFullName($billingAddress);
        $data['countryBilling']              = $this->getCountryById($billingAddress);
        $data[$typeTitle . 'Note']           = $this->helperData->getPdfNote($type, $storeId);
        $data['commentText']                 = $this->getCommentText($order);
        $data['commentLabel']                = 'Notes for this Order';
        $data['header4']                     = $imgHeaderUrl->getData('header4');
        $data['header5']                     = $imgHeaderUrl->getData('header5');
        $data['customer_name']               = $order->getCustomerName();
        $data['order_date']                  = $this->customFunction->formatDate($order->getData('created_at'));
        if ($type !== 'order') {
            $data[$type . '_date'] = $this->customFunction->formatDate($data[$type]->getData('created_at'));
        }
        if ($type === 'shipment') {
            $data['shipment_amount'] = $order->formatPriceTxt($order->getShippingAmount());
        } else {
            $data[$type . '_amount'] = $order->formatPriceTxt($data[$type]->getGrandTotal());
        }
        $data['shipping_amount'] = $order->formatPriceTxt($order->getShippingAmount());
        $data['subtotal']        = $order->formatPriceTxt($order->getSubtotal());
        $data['grand_total']     = $order->formatPriceTxt($order->getGrandTotal());
        $data['total_due']       = $order->formatPriceTxt($order->getTotalDue());
        $data['tax_amount']      = $order->formatPriceTxt($order->getTaxAmount());
        $data['isNotVirtual']    = $order->getIsNotVirtual();

        return $this->addCustomTemplateVars($data, $storeId);
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public function getCommentText($order)
    {
        $commentText = '';
        $comments    = [];
        foreach ($order->getStatusHistoryCollection() as $comment) {
            if ($comment->getIsVisibleOnFront()) {
                $comments[] = $comment->getComment();
            }
        }
        for ($i = 0; $i < count($comments); $i++) {
            if ($i === count($comments) - 1) {
                $commentText .= $comments[$i];
            } else {
                $commentText .= $comments[$i] . " | ";
            }
        }

        return $commentText;
    }

    /**
     * @param Address $address
     *
     * @return bool|string
     */
    public function getFullName($address)
    {
        if ($address) {
            if ($address->getMiddlename()) {
                return $address->getFirstname() . ' ' .
                    $address->getMiddlename() . ' ' .
                    $address->getLastName();
            }

            return $address->getFirstname() . ' ' .
                $address->getLastName();
        }

        return '';
    }

    /**
     * @param Order $order
     * @param string $type
     *
     * @return object
     */
    public function getCollection($order, $type)
    {
        switch ($type) {
            case Type::CREDIT_MEMO:
                $collection = $order->getCreditmemosCollection();
                break;
            case Type::SHIPMENT:
                $collection = $order->getShipmentsCollection();
                break;
            default:
                $collection = $order->getInvoiceCollection();
        }

        return $collection;
    }

    /**
     * @param string $type
     * @param int $id
     *
     * @return string
     * @throws Exception
     * @throws LocalizedException
     */
    public function printPdf($type, $id)
    {
        $data    = $this->getDataProcess($type, $id);
        $store   = $data['store'];
        $storeId = $this->checkStoreId($store->getId());
        switch ($type) {
            case Type::CREDIT_MEMO:
                $templateId = $this->helperData->getPdfTemplate(Type::CREDIT_MEMO, $storeId);
                break;
            case Type::ORDER:
                $templateId = $this->helperData->getPdfTemplate(Type::ORDER, $storeId);
                break;
            case Type::SHIPMENT:
                $templateId = $this->helperData->getPdfTemplate(Type::SHIPMENT, $storeId);
                break;
            default:
                $templateId = $this->helperData->getPdfTemplate(Type::INVOICE, $storeId);
        }
        $templateHtml = $this->getTemplateHtml($templateId, $type);

        return $this->getPDFContent($templateHtml, $data, 'D', $storeId);
    }

    /**
     * Get font directory
     *
     * @return string
     */
    public function getFontDirectory()
    {
        return $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Mageplaza_PdfInvoice') . '/Fonts';
    }

    /**
     * Get base template path
     *
     * @return string
     */
    public function getBaseTemplatePath()
    {
        // Get directory of Data.php
        $currentDir = __DIR__;

        // Get root directory(path of magento's project folder)
        $rootPath = $this->directoryList->getRoot();

        $currentDirArr = explode('\\', $currentDir);
        if (count($currentDirArr) === 1) {
            $currentDirArr = explode('/', $currentDir);
        }

        $rootPathArr = explode('/', $rootPath);
        if (count($rootPathArr) === 1) {
            $rootPathArr = explode('\\', $rootPath);
        }

        $basePath           = '';
        $rootPathArrCount   = count($rootPathArr);
        $currentDirArrCount = count($currentDirArr);
        for ($i = $rootPathArrCount; $i < $currentDirArrCount - 1; $i++) {
            $basePath .= $currentDirArr[$i] . '/';
        }

        return $basePath . 'view/base/templates/default/';
    }

    /**
     * @param string $type
     * @param Order $object
     * @param null $storeId
     *
     * @return bool
     */
    public function isAllowCustomerGroup($type, $object, $storeId = null)
    {
        if (!$this->helperData->applyForGroups($type, $storeId)) {
            return true;
        }

        $customerId = $object->getCustomerId();

        if ($customerId) {
            $customerGroup = $this->customerFactory->create()->load($customerId)->getGroupId();
        } else {
            $customerGroup = '0';
        }

        return in_array($customerGroup, explode(',', $this->helperData->allowCustomerGroups($type, $storeId)), true);
    }

    /**
     * @param OrderAddressInterface|null $address
     *
     * @return string
     */
    public function getStreet($address)
    {
        if ($address) {
            $street = $address->getStreet();
            if (is_array($street)) {
                $streetString = '';
                for ($i = 0; $i < count($street); $i++) {
                    $streetString .= ' ' . trim($street[$i]);
                }

                return $streetString;
            } else {
                if (is_string($street)) {
                    return $street;
                }
            }
        }

        return '';
    }

    /**
     * @param OrderAddressInterface|null $address
     *
     * @return string
     */
    public function getCountryById(?OrderAddressInterface $address)
    {
        if ($address) {
            $countryId = $address->getCountryId();
            if ($countryId) {
                return $this->_countryFactory->create()->loadByCode($countryId)->getName();
            }
        }

        return '';
    }

    /**
     * @return DataObject
     */
    public function getHeaderImgUrl()
    {
        return new DataObject([
            'header4' => $this->_assetRepo->getUrl("Mageplaza_PdfInvoice::images/header/header-template4.png"),
            'header5' => $this->_assetRepo->getUrl("Mageplaza_PdfInvoice::images/header/header-template5.png"),
        ]);
    }

    /**
     * @param $orderId
     * @param $type
     *
     * @return bool
     */
    public function validateCondition($type, $orderId)
    {
        return $this->helperData->validateConditionRule($type, $orderId);
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getCurrentStore()
    {
        return $this->storeManager->getStore();
    }
}
