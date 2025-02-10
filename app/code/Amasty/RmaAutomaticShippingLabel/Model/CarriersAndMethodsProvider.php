<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Model;

use Amasty\Rma\Model\OptionSource\ItemStatus;
use Amasty\Rma\Model\Request\Repository;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Shipping;
use Magento\Shipping\Model\ShippingFactory;
use Magento\Store\Model\StoreManagerInterface;

class CarriersAndMethodsProvider
{
    public const AVAILABLE_METHODS = [
        \Magento\Dhl\Model\Carrier::CODE,
        \Magento\Fedex\Model\Carrier::CODE,
        \Magento\Ups\Model\Carrier::CODE
    ];

    /**
     * @var Repository
     */
    private $rmaRepository;

    /**
     * @var CollectionFactory
     */
    private $orderItemCollectionFactory;

    /**
     * @var ItemFactory
     */
    private $quoteItemFactory;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var ReturnAddressResolver
     */
    private $returnAddressResolver;

    /**
     * @var RateRequestFactory
     */
    private $rateRequestFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ShippingFactory
     */
    private $shippingFactory;

    /**
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * Carriers storage
     * @var array
     */
    protected $carriers = [];

    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    public function __construct(
        Repository $rmaRepository,
        CollectionFactory $orderItemCollectionFactory,
        ItemFactory $quoteItemFactory,
        QuoteFactory $quoteFactory,
        ReturnAddressResolver $returnAddressResolver,
        RateRequestFactory $rateRequestFactory,
        StoreManagerInterface $storeManager,
        ShippingFactory $shippingFactory,
        RateFactory $rateFactory,
        CarrierFactory $carrierFactory
    ) {
        $this->rmaRepository = $rmaRepository;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->quoteFactory = $quoteFactory;
        $this->returnAddressResolver = $returnAddressResolver;
        $this->rateRequestFactory = $rateRequestFactory;
        $this->storeManager = $storeManager;
        $this->shippingFactory = $shippingFactory;
        $this->rateFactory = $rateFactory;
        $this->carrierFactory = $carrierFactory;
    }

    public function getCarriersAndMethodsWithShippingLabels(int $rmaId): array
    {
        $rma = $this->rmaRepository->getById($rmaId);
        $allMethods = $this->getAllAvailableShippingMethods($rma);
        $result = [];

        /** @var Rate $method */
        foreach ($allMethods as $method) {
            $carrier = $this->getCarrier($method->getCode(), $rma->getStoreId());

            if ($carrier->isShippingLabelsAvailable()) {
                $result[$carrier->getCarrierCode()]['title'] = $method->getCarrierTitle();
                $result[$carrier->getCarrierCode()]['code'] = $method->getCarrier();
                $result[$carrier->getCarrierCode()]['methods'][] = [
                    'title' => $method->getMethodTitle(),
                    'code' => $method->getCode(),
                    'price' => $method->getPrice()
                ];
            }
        }

        return $result;
    }

    public function getCarrier(string $code, int $storeId): AbstractCarrier
    {
        $carrierCode = explode('_', $code, 2)[0];

        if (empty($this->carriers[$carrierCode])) {
            $carrier = $this->carrierFactory->create($carrierCode, $storeId);
            $this->carriers[$carrierCode] = $carrier;
        }

        return $this->carriers[$carrierCode];
    }

    private function getAllAvailableShippingMethods(\Amasty\Rma\Api\Data\RequestInterface $rma): array
    {
        $methods = [];
        $approvedItems = [];

        foreach ($rma->getRequestItems() as $requestItem) {
            if ($requestItem->getItemStatus() !== ItemStatus::AUTHORIZED) {
                continue;
            }
            $approvedItems[$requestItem->getOrderItemId()] = $requestItem;
        }

        if ($approvedItems) {
            $orderItemCollection = $this->orderItemCollectionFactory->create();
            $orderItemCollection->addFieldToFilter('item_id', ['IN' => array_keys($approvedItems)]);
            $quoteItems = [];
            $subtotal = $weight = $qty = $storeId = 0;
            $address = null;

            foreach ($orderItemCollection->getItems() as $orderItem) {
                $quoteItem = $this->quoteItemFactory->create();

                $orderItem->setQty($approvedItems[$orderItem->getItemId()]->getQty());
                $orderItem->setRowTotal($orderItem->getPrice() * $orderItem->getQty());
                $orderItem->setBaseRowTotal($orderItem->getBasePrice() * $orderItem->getQty());
                $orderItem->setRowTotalWithDiscount(0);
                $orderItem->setRowWeight($orderItem->getWeight() * $orderItem->getQty());
                $orderItem->setPriceInclTax($orderItem->getPrice());
                $orderItem->setBasePriceInclTax($orderItem->getBasePrice());
                $orderItem->setRowTotalInclTax($orderItem->getRowTotal());
                $orderItem->setBaseRowTotalInclTax($orderItem->getBaseRowTotal());

                $quoteItems[] = $quoteItem->addData($orderItem->toArray());
                $subtotal += $orderItem->getBaseRowTotal();
                $weight += $orderItem->getRowWeight();
                $qty += $orderItem->getQty();
                $storeId = (int)$orderItem->getStoreId();
                $address = $orderItem->getOrder()->getShippingAddress();

                $quote = $this->quoteFactory->create();
                $quote->setStoreId($storeId);
                $quoteItem->setQuote($quote);
            }
            $methods = $this->getShippingRates($quoteItems, $storeId, $subtotal, $weight, $qty, $address);
        }

        return $methods;
    }

    private function getShippingRates(
        array $items,
        int $storeId,
        float $subtotal,
        float $weight,
        float $qty,
        Address $address = null
    ): array {
        $shippingDestination = $this->returnAddressResolver->getReturnAddress($storeId);

        /** @var RateRequest $rateRequest */
        $rateRequest = $this->rateRequestFactory->create();
        $rateRequest->setAllItems($items)
            ->setDestCountryId($shippingDestination->getCountryId())
            ->setDestRegionId($shippingDestination->getRegionId())
            ->setDestRegionCode((string)$shippingDestination->getRegionId())
            ->setDestStreet($shippingDestination->getStreetFull())
            ->setDestCity($shippingDestination->getCity())
            ->setDestPostcode($shippingDestination->getPostcode())
            ->setDestCompanyName($shippingDestination->getCompany());

        $rateRequest->setPackageValue($subtotal);
        $rateRequest->setPackageValueWithDiscount($subtotal);
        $rateRequest->setPackageWeight($weight);
        $rateRequest->setPackageQty($qty);

        //shop destination address data
        //different carriers use different variables.
        $rateRequest->setOrigCountryId($address->getCountryId())
            ->setOrigCountry($address->getCountryId())
            ->setOrigState($address->getRegionId())
            ->setOrigRegionCode($address->getRegionId())
            ->setOrigCity($address->getCity())
            ->setOrigPostcode($address->getPostcode())
            ->setOrigPostal($address->getPostcode())
            ->setOrigCompanyName($address->getCompany() ? $address->getCompany() : __('NA'))
            ->setOrig(true);
        $rateRequest->setPackagePhysicalValue($subtotal);
        $rateRequest->setFreeMethodWeight(0);

        $store = $this->storeManager->getStore($storeId);
        $rateRequest->setStoreId($store->getId());
        $rateRequest->setWebsiteId($store->getWebsiteId());
        $rateRequest->setBaseCurrency($store->getBaseCurrency());
        $rateRequest->setPackageCurrency($store->getCurrentCurrency());
        $rateRequest->setIsReturn(true);

        /** @var $shipping Shipping */
        $shipping = $this->shippingFactory->create();
        $result = $shipping->setCarrierAvailabilityConfigField('active_amrma')
            ->collectRates($rateRequest)
            ->getResult();
        $availableRates = [];

        if ($result) {
            $shippingRates = $result->getAllRates();

            foreach ($shippingRates as $shippingRate) {
                if (in_array($shippingRate->getCarrier(), self::AVAILABLE_METHODS)
                    && !$shippingRate->getErrorMessage()
                ) {
                    /** @var $addressRate Rate */
                    $addressRate = $this->rateFactory->create();
                    $availableRates[] = $addressRate->importShippingRate($shippingRate);
                }
            }
        }

        return $availableRates;
    }
}
