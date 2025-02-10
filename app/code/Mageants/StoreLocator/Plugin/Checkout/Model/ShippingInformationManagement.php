<?php
/**
 * @category Mageants StoreLocator
 * @package Mageants_StorePickup
 * @copyright Copyright (c) 2017 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\StoreLocator\Plugin\Checkout\Model;

class ShippingInformationManagement
{
    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) 
    {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) 
    {
        $extAttributes = $addressInformation->getExtensionAttributes();
        if($extAttributes->getPickupStore())
        {
            $pickupDate = $extAttributes->getPickupDate();
            $pickupStore = $extAttributes->getPickupStore();
            $quote = $this->quoteRepository->getActive($cartId);
            $quote->setPickupDate($pickupDate);
            $quote->setPickupStore($pickupStore);
        }
    }
}
