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
 * @package     Mageplaza_TableRateShipping
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\TableRateShipping\Plugin\Helper;

use Closure;
use Magento\Catalog\Helper\Data;
//use Magento\Tax\Api\Data\TaxClassKeyInterface;
//use Magento\Tax\Model\Config;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Model\Config;

class TaxPrice extends Data
{
    public function aroundGetTaxPrice(
        Data $subject,
        Closure $proceed,
        $product,
        $price,
        $includingTax = null,
        $shippingAddress = null,
        $billingAddress = null,
        $ctc = null,
        $store = null,
        $priceIncludesTax = null,
        $roundPrice = true
    ){
        if (!$price) {
            return $price;
        }

        $store = $subject->_storeManager->getStore($store);
        if ($subject->_taxConfig->needPriceConversion($store)) {
            if ($priceIncludesTax === null) {
                $priceIncludesTax = $subject->_taxConfig->priceIncludesTax($store);
            }

            $shippingAddressDataObject = null;
            if ($shippingAddress === null) {
                $shippingAddressDataObject =
                    $this->convertDefaultTaxAddress($subject->_customerSession->getDefaultTaxShippingAddress());
            } elseif ($shippingAddress instanceof \Magento\Customer\Model\Address\AbstractAddress) {
                $shippingAddressDataObject = $shippingAddress->getDataModel();
            }

            $billingAddressDataObject = null;
            if ($billingAddress === null) {
                $billingAddressDataObject =
                    $this->convertDefaultTaxAddress($subject->_customerSession->getDefaultTaxBillingAddress());
            } elseif ($billingAddress instanceof \Magento\Customer\Model\Address\AbstractAddress) {
                $billingAddressDataObject = $billingAddress->getDataModel();
            }

            $taxClassKey = $subject->_taxClassKeyFactory->create();
            $taxClassKey->setType(TaxClassKeyInterface::TYPE_ID)
                ->setValue($product->getTaxClassId());

            if ($ctc === null && $subject->_customerSession->getCustomerGroupId() != null) {
                $ctc = $subject->customerGroupRepository->getById($subject->_customerSession->getCustomerGroupId())
                    ->getTaxClassId();
            }

            $customerTaxClassKey = $subject->_taxClassKeyFactory->create();
            $customerTaxClassKey->setType(TaxClassKeyInterface::TYPE_ID)
                ->setValue($ctc);

            $item = $subject->_quoteDetailsItemFactory->create();
            $item->setQuantity(1)
                ->setCode($product->getSku())
                ->setShortDescription($product->getShortDescription())
                ->setTaxClassKey($taxClassKey)
                ->setIsTaxIncluded($priceIncludesTax)
                ->setType('product')
                ->setUnitPrice($price);

            $quoteDetails = $subject->_quoteDetailsFactory->create();
            $quoteDetails->setShippingAddress($shippingAddressDataObject)
                ->setBillingAddress($billingAddressDataObject)
                ->setCustomerTaxClassKey($customerTaxClassKey)
                ->setItems([$item])
                ->setCustomerId($subject->_customerSession->getCustomerId());

            $storeId = null;
            if ($store) {
                $storeId = $store->getId();
            }
            $taxDetails = $subject->_taxCalculationService->calculateTax($quoteDetails, $storeId, $roundPrice);
            $items = $taxDetails->getItems();
            $taxDetailsItem = array_shift($items);

            if ($includingTax !== null) {
                if ($includingTax) {
                    $price = $taxDetailsItem->getPriceInclTax();
                } else {
                    $price = $taxDetailsItem->getPrice();
                }
            } else {
                switch ($subject->_taxConfig->getPriceDisplayType($store)) {
                    case Config::DISPLAY_TYPE_EXCLUDING_TAX:
                    case Config::DISPLAY_TYPE_BOTH:
                        $price = $taxDetailsItem->getPrice();
                        break;
                    case Config::DISPLAY_TYPE_INCLUDING_TAX:
                        $price = $taxDetailsItem->getPriceInclTax();
                        break;
                    default:
                        break;
                }
            }
        }

        if (!$roundPrice) {
            return $subject->priceCurrency->round($price);
        } else {
            return $price;
        }
    }

    private function convertDefaultTaxAddress(array $taxAddress = null)
    {
        if (empty($taxAddress)) {
            return null;
        }
        $addressDataObject = $this->addressFactory->create()
            ->setCountryId($taxAddress['country_id'])
            ->setPostcode($taxAddress['postcode']);

        if (isset($taxAddress['region_id'])) {
            $addressDataObject->setRegion($this->regionFactory->create()->setRegionId($taxAddress['region_id']));
        }
        return $addressDataObject;
    }
}
