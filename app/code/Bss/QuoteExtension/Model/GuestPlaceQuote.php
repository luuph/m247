<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model;

use Bss\QuoteExtension\Api\GuestPlaceQuoteInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Bss\QuoteExtension\Api\PlaceQuoteInterface;
use Bss\QuoteExtension\Helper\Data;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Customer\Model\Session;

/**
 * Class PlaceQuote
 */
class GuestPlaceQuote implements GuestPlaceQuoteInterface
{
    /**
     * @var PlaceQuoteInterface
     */
    protected $placeQuote;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * PlaceQuote constructor.
     *
     * @param PlaceQuoteInterface $placeQuote
     * @param Data $data
     * @param Session $customerSession
     */
    public function __construct(
        PlaceQuoteInterface $placeQuote,
        Data $data,
        Session $customerSession
    ) {
        $this->placeQuote = $placeQuote;
        $this->data = $data;
        $this->customerSession = $customerSession;
    }

    /**
     * Set shipping information and place quote for a specified quote cart.
     * @inheridoc
     * @return int|void
     * @throws InputException
     */
    public function saveShippingInformationAndPlaceQuote(
        $cartId,
        $customerNote,
        ShippingMethodInterface $shippingMethod,
        AddressInterface $shippingAddress,
        $additionalData
    ) {
        $quoteId = $this->getQuoteId($cartId);
        $this->validateInformationGuest($additionalData);
        if ($this->data->isRequiredAddress() && !$this->customerSession->isLoggedIn()) {
            $additionalData['customer_firstname'] = $shippingAddress->getData('firstname');
            $additionalData['customer_lastname'] = $shippingAddress->getData('lastname');
        }
        $this->placeQuote->saveShippingInformationAndPlaceQuote(
            $quoteId,
            $customerNote,
            $shippingMethod,
            $shippingAddress,
            $additionalData
        );
    }

    /**
     * Validate information of g
     *
     * @param array $addtionalData
     * @throws InputException
     */
    public function validateInformationGuest($addtionalData)
    {
        if (!isset($addtionalData["email"])) {
            throw new InputException(__('Input email is required'));
        } elseif (!filter_var($addtionalData["email"], FILTER_VALIDATE_EMAIL)) {
            throw new InputException(__('Email has a wrong format'));
        }
        if (!$this->data->isRequiredAddress()) {
            if (!isset($addtionalData["customer_firstname"]) || (isset($addtionalData["customer_firstname"]) && !$addtionalData["customer_firstname"])) {
                throw new InputException(__('Input Firstname is required'));
            }

            if (!isset($addtionalData["customer_lastname"]) || (isset($addtionalData["customer_lastname"]) && !$addtionalData["customer_lastname"])) {
                throw new InputException(__('Input lastname is required'));
            }
        }
    }

    /**
     * Get quote by mask_id
     *
     * @param string|int $cartId
     * @return string|int
     */
    public function getQuoteId($cartId)
    {
        return $cartId;
    }
}
