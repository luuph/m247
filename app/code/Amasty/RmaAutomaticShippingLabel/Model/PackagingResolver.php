<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Model;

use Amasty\Rma\Model\Request\Repository;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Shipping\Model\Carrier\Source\GenericInterface;
use Magento\Shipping\Model\CarrierFactory;

class PackagingResolver
{
    public const LABEL = 'label';
    public const NAME = 'value';
    public const TYPE = 'type';
    public const OPTIONS = 'options';
    public const IS_HIDDEN = 'isHidden';

    public const WEIGHT_MEASURE = 'weight';
    public const DIMENSION_MEASURE = 'dimension';

    // compatibility after deleting \Zend_Measure_Weight and \Zend_Measure_Length in Magento v2.4.6.
    public const POUND = 'POUND';
    public const KILOGRAM = 'KILOGRAM';
    public const INCH = 'INCH';
    public const CENTIMETER = 'CENTIMETER';

    /**
     * @var GenericInterface
     */
    private $sourceSizeModel;

    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var CarriersAndMethodsProvider
     */
    private $carriersProvider;

    /**
     * @var Repository
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

    public function __construct(
        GenericInterface $sourceSizeModel,
        CarrierFactory $carrierFactory,
        Json $json,
        CarriersAndMethodsProvider $carriersProvider,
        Repository $rmaRepository,
        ReturnAddressResolver $returnAddressResolver,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->sourceSizeModel = $sourceSizeModel;
        $this->carrierFactory = $carrierFactory;
        $this->json = $json;
        $this->carriersProvider = $carriersProvider;
        $this->rmaRepository = $rmaRepository;
        $this->returnAddressResolver = $returnAddressResolver;
        $this->orderRepository = $orderRepository;
    }

    public function getPackagingData(int $rmaId, string $method): string
    {
        $packageParams = [];
        $sizeSource = $this->getSourceSizeModel();
        $isGirthEnabled = $this->isGirthAllowed($rmaId, $method);
        $hideCustomsValue = $this->isHideCustomsValue($rmaId);
        $containers = $this->getMethodContainers($rmaId, $method);
        $deliveryConfirmationTypes = $this->getDeliveryConfirmationTypes($rmaId, $method);

        if ($containers) {
            $packageParams[] = $this->prepareParam('Package Type', 'container', 'select', $containers);
        }

        if ($isGirthEnabled && $sizeSource) {
            $packageParams[] = $this->prepareParam('Size', 'package_size', 'select', $sizeSource);
            $packageParams[] = $this->prepareParam('Girth', 'container_girth', 'text');
            $packageParams[] = $this->prepareParam(
                '',
                'container_girth_dimension_units',
                'select',
                $this->getMeasureUnits(self::DIMENSION_MEASURE)
            );
        }
        $packageParams[] = $this->prepareParam(
            'Customs Value',
            'customs_value',
            'text',
            [],
            $hideCustomsValue
        );

        $packageParams[] = $this->prepareParam('Total Weight', 'weight', 'text');
        $packageParams[] = $this->prepareParam(
            '',
            'weight_units',
            'select',
            $this->getMeasureUnits(self::WEIGHT_MEASURE)
        );
        $packageParams[] = $this->prepareParam('Length', 'length', 'text');
        $packageParams[] = $this->prepareParam('Width', 'width', 'text');
        $packageParams[] = $this->prepareParam('Height', 'height', 'text');
        $packageParams[] = $this->prepareParam(
            '',
            'dimension_units',
            'select',
            $this->getMeasureUnits(self::DIMENSION_MEASURE)
        );

        if ($deliveryConfirmationTypes) {
            $packageParams[] = $this->prepareParam(
                'Signature Confirmation',
                'delivery_confirmation',
                'select',
                $deliveryConfirmationTypes
            );
        }

        return $this->json->serialize($packageParams);
    }

    private function getSourceSizeModel(): array
    {
        return $this->sourceSizeModel->toOptionArray();
    }

    private function isGirthAllowed(int $rmaId, string $method): bool
    {
        try {
            $rmaModel = $this->rmaRepository->getById($rmaId);
            $storeId = $rmaModel->getStoreId();
            list($carrierCode, $methodCode) = explode('_', $method, 2);
            $carrier = $this->carriersProvider->getCarrier($carrierCode, $storeId);
            $countryId = $this->returnAddressResolver->getReturnAddress($storeId)->getCountryId();

            return $carrier->isGirthAllowed($countryId, $method);
        } catch (LocalizedException $e) {
            return false;
        }
    }

    protected function isHideCustomsValue(int $rmaId): bool
    {
        try {
            $rmaModel = $this->rmaRepository->getById($rmaId);
            $storeId = $rmaModel->getStoreId();
            $shipperAddressCountryId = $this->returnAddressResolver->getReturnAddress($storeId)->getCountryId();
            $orderAddress = $this->orderRepository->get((int)$rmaModel->getOrderId())->getShippingAddress();

            return $shipperAddressCountryId == $orderAddress->getCountryId();
        } catch (LocalizedException $e) {
            return true;
        }
    }

    private function getMethodContainers(int $rmaId, string $method): array
    {
        try {
            $rmaModel = $this->rmaRepository->getById($rmaId);
            $storeId = $rmaModel->getStoreId();
            $orderAddress = $this->orderRepository->get($rmaModel->getOrderId())->getShippingAddress();

            list($carrierCode, $methodCode) = explode('_', $method, 2);
            $countryShipper = $this->returnAddressResolver->getReturnAddress($storeId)->getCountryId();
            $carrier = $this->carriersProvider->getCarrier($carrierCode, $storeId);
            $params = new DataObject(
                [
                    'method' => $methodCode,
                    'country_shipper' => $countryShipper,
                    'country_recipient' => $orderAddress->getCountryId()
                ]
            );

            return $carrier->getContainerTypes($params);
        } catch (LocalizedException $e) {
            return [];
        }
    }

    public function getDeliveryConfirmationTypes(int $rmaId, string $method): array
    {
        $rmaModel = $this->rmaRepository->getById($rmaId);
        $storeId = $rmaModel->getStoreId();
        $countryId = $this->orderRepository->get($rmaModel->getOrderId())->getShippingAddress()->getCountryId();

        list($carrierCode, $methodCode) = explode('_', $method, 2);
        $carrier = $this->carriersProvider->getCarrier($carrierCode, $storeId);
        $params = new \Magento\Framework\DataObject(['country_recipient' => $countryId]);

        if (is_array($carrier->getDeliveryConfirmationTypes($params))) {
            return $carrier->getDeliveryConfirmationTypes($params);
        }

        return [];
    }

    private function getMeasureUnits(string $measureType): array
    {
        switch ($measureType) {
            case self::WEIGHT_MEASURE:
                $units = [
                    self::POUND => __('lb'),
                    self::KILOGRAM => __('kg')
                ];
                break;
            case self::DIMENSION_MEASURE:
                $units = [
                    self::INCH => __('in'),
                    self::CENTIMETER => __('cm')
                ];
                break;
            default:
                $units = [];
        }

        return $units;
    }

    private function prepareParam(
        string $label = '',
        string $name = '',
        string $type = '',
        array $options = [],
        bool $isHidden = false
    ): array {
        $optionsArray = [];

        foreach ($options as $key => $value) {
            $optionsArray[] = [
                'name' => $key,
                'value' => $value
            ];
        }
        return [
            self::LABEL => __($label),
            self::NAME => $name,
            self::TYPE => $type,
            self::OPTIONS => $optionsArray,
            self::IS_HIDDEN => $isHidden
        ];
    }
}
