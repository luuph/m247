<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Model\ShippingLabel;

use Amasty\Rma\Api\Data\RequestInterface;
use Amasty\Rma\Api\RequestRepositoryInterface;
use Amasty\Rma\Model\Request\Request;
use Amasty\RmaAutomaticShippingLabel\Model\ReturnAddressResolver;
use Amasty\RmaAutomaticShippingLabel\Utils\Pdf;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Shipment\ReturnShipmentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject;

class ShippingLabelManagement
{
    /**
     * @var Repository
     */
    private $labelRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $rmaRepository;

    /**
     * @var ReturnAddressResolver
     */
    private $returnAddressResolver;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ReturnShipmentFactory
     */
    private $returnShipmentFactory;

    /**
     * @var Pdf
     */
    private $pdf;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        Repository $labelRepository,
        RequestRepositoryInterface $rmaRepository,
        ReturnAddressResolver $returnAddressResolver,
        OrderRepositoryInterface $orderRepository,
        CarrierFactory $carrierFactory,
        StoreManagerInterface $storeManager,
        ReturnShipmentFactory $returnShipmentFactory,
        Filesystem $filesystem,
        Pdf $pdf
    ) {
        $this->labelRepository = $labelRepository;
        $this->rmaRepository = $rmaRepository;
        $this->returnAddressResolver = $returnAddressResolver;
        $this->orderRepository = $orderRepository;
        $this->carrierFactory = $carrierFactory;
        $this->storeManager = $storeManager;
        $this->returnShipmentFactory = $returnShipmentFactory;
        $this->pdf = $pdf;
        $this->filesystem = $filesystem;
    }

    public function createShippingLabel(int $rmaId, array $data = [])
    {
        if (empty($data['packages'])) {
            throw new LocalizedException(__('No packages was provided.'));
        }
        $rma = $this->rmaRepository->getById($rmaId);

        try {
            $model = $this->labelRepository->getByRequestId($rmaId);//only one label for each rma
        } catch (NoSuchEntityException $e) {
            $model = $this->labelRepository->getEmptyShippingLabelModel();
        }
        $model->setPackages($data['packages']);
        $model->setPrice((float)$data['price']);
        $model->setRequestId($rmaId);

        list($carrierCode, $methodCode) = explode('_', $data['code'], 2);
        $carrier = $this->carrierFactory->create($carrierCode, $rma->getStoreId());
        $model->setCarrierCode($carrier->getCarrierCode());
        $model->setCarrierTitle($carrier->getConfigData('title'));
        $model->setCarrierMethod($methodCode);
        $weight = 0;

        foreach ($data['packages'] as $package) {
            $weight += $package['params']['weight'];
        }
        $shipmentResponse = $this->sendShipmentRequest($rma, $data['code'], $weight, $data['packages']);

        if ($shipmentResponse->hasErrors() || !$shipmentResponse->hasInfo()) {
            throw new LocalizedException(__($shipmentResponse->getErrors()));
        }
        $labelsContent = [];
        $trackingNumbers = [];

        foreach ($shipmentResponse->getInfo() as $info) {
            if (!empty($info['tracking_number']) && !empty($info['label_content'])) {
                $labelsContent[] = $info['label_content'];

                if (is_array($info['tracking_number'])) {
                    foreach ($info['tracking_number'] as $number) {
                        $trackingNumbers[] = $number;
                    }
                } else {
                    $trackingNumbers[] = $info['tracking_number'];
                }
            }
        }
        $pdfOutput = $this->pdf->combineLabelsPdf($labelsContent);
        $model->setShippingLabel($pdfOutput->render());
        $this->labelRepository->save($model);
        $this->saveLabelForRma($rma, $pdfOutput->render());

        foreach ($trackingNumbers as $trackingNumber) {
            $tracking = $this->rmaRepository->getEmptyTrackingModel();
            $tracking->setRequestId($rmaId)
                ->setIsCustomer(false)
                ->setTrackingCode($carrierCode)
                ->setTrackingNumber($trackingNumber);
            $this->rmaRepository->saveTracking($tracking);
        }
    }

    /**
     * Get pdf content of shipping label if it exists
     */
    public function getShippingLabelByRmaIdPdf(int $rmaId): string
    {
        $labelContent = $this->labelRepository->getByRequestId($rmaId)->getShippingLabel();

        if ($labelContent) {
            if (stripos($labelContent, '%PDF-') !== false) {
                $pdfContent = $labelContent;
            } else {
                $pdf = new \Zend_Pdf();
                $page = $this->pdf->createPdfPageFromImageString($labelContent);

                if (!$page) {
                    throw new LocalizedException(
                        __("Wrong file extension in shipment for request %1.", $rmaId)
                    );
                }
                $pdf->pages[] = $page;
                $pdfContent = $pdf->render();
            }

            return $pdfContent;
        } else {
            throw new LocalizedException(__('Shipping Label does not exists.'));
        }
    }

    private function saveLabelForRma(RequestInterface $rma, string $labelContent = '')
    {
        $mediaWriter = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $labelName = 'ShippingLabel(' . $rma->getRequestId() . ').pdf';
        $labelPath = \Amasty\Rma\Utils\FileUpload::MEDIA_PATH . $rma->getRequestId() . DIRECTORY_SEPARATOR;

        if (!$labelContent) {
            $labelContent = $this->labelRepository->getByRequestId($rma->getRequestId())->getShippingLabel();
        }

        if (!$labelContent) {
            throw new LocalizedException(__('Shipping Label does not exists.'));
        }
        $pdfContent = $this->pdf->combineLabelsPdf([$labelContent]);

        if (!$mediaWriter->isExist($labelPath)) {
            $mediaWriter->create($labelPath);
        }
        $pdfContent->save(
            $mediaWriter->getAbsolutePath($labelPath . $labelName)
        );

        $rma->setShippingLabel($labelName);
        $this->rmaRepository->save($rma);
    }

    private function sendShipmentRequest(
        Request $rma,
        string $carrierCode,
        float $weight,
        array $packages
    ): DataObject {
        $order = $this->orderRepository->get($rma->getOrderId());
        $shipperAddress = $order->getShippingAddress();
        $recipientAddress = $this->returnAddressResolver->getReturnAddress($rma->getStoreId());
        list($carrierCode, $shippingMethod) = explode('_', $carrierCode, 2);

        $carrier = $this->carrierFactory->create($carrierCode, $rma->getStoreId());
        $baseCurrencyCode = $this->storeManager->getStore($rma->getStoreId())->getBaseCurrencyCode();
        $shipperRegionCode = $shipperAddress->getRegionCode();
        $recipientRegionCode = (string)$recipientAddress->getRegionId();
        $recipientContactName = $this->returnAddressResolver->getReturnContactName($rma->getStoreId());

        /** @var $request \Magento\Shipping\Model\Shipment\ReturnShipment */
        $request = $this->returnShipmentFactory->create();

        $request->setShipperContactPersonName($order->getCustomerName());
        $request->setShipperContactPersonFirstName($order->getCustomerFirstname());
        $request->setShipperContactPersonLastName($order->getCustomerLastname());
        $companyName = $shipperAddress->getCompany();

        if (empty($companyName)) {
            $companyName = $order->getCustomerName();
        }
        $request->setShipperContactCompanyName($companyName);
        $request->setShipperContactPhoneNumber($shipperAddress->getTelephone());
        $request->setShipperEmail($shipperAddress->getEmail());
        $request->setShipperAddressStreet(
            trim($shipperAddress->getStreetLine(1) . ' ' . $shipperAddress->getStreetLine(2))
        );
        $request->setShipperAddressStreet1($shipperAddress->getStreetLine(1));
        $request->setShipperAddressStreet2($shipperAddress->getStreetLine(2));
        $request->setShipperAddressCity($shipperAddress->getCity());
        $request->setShipperAddressStateOrProvinceCode($shipperRegionCode);
        $request->setShipperAddressPostalCode($shipperAddress->getPostcode());
        $request->setShipperAddressCountryCode($shipperAddress->getCountryId());

        $request->setRecipientContactPersonName($recipientContactName->getName());
        $request->setRecipientContactPersonFirstName($recipientContactName->getFirstName());
        $request->setRecipientContactPersonLastName($recipientContactName->getLastName());
        $request->setRecipientContactCompanyName($recipientAddress->getCompany());
        $request->setRecipientContactPhoneNumber($recipientAddress->getTelephone());
        $request->setRecipientEmail($recipientAddress->getEmail());
        $request->setRecipientAddressStreet($recipientAddress->getStreetFull());
        $request->setRecipientAddressStreet1($recipientAddress->getStreetLine(1));
        $request->setRecipientAddressStreet2($recipientAddress->getStreetLine(2));
        $request->setRecipientAddressCity($recipientAddress->getCity());
        $request->setRecipientAddressStateOrProvinceCode($recipientRegionCode);
        $request->setRecipientAddressRegionCode($recipientRegionCode);
        $request->setRecipientAddressPostalCode($recipientAddress->getPostcode());
        $request->setRecipientAddressCountryCode($recipientAddress->getCountryId());

        $request->setShippingMethod($shippingMethod);
        $request->setPackageWeight($weight);
        $request->setPackages($packages);
        $request->setBaseCurrencyCode($baseCurrencyCode);
        $request->setStoreId($rma->getStoreId());

        $referenceData = 'RMA #' . $order->getIncrementId();
        $request->setReferenceData($referenceData);

        return $carrier->returnOfShipment($request);
    }
}
