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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Model;

use Bss\HidePrice\Api\ManagementInterface;
use Bss\HidePrice\Helper\Data as HelperData;
use Bss\HidePrice\Helper\Label as HelperLabel;
use Bss\HidePrice\Helper\CartHidePrice;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class Management
 *
 * @package Bss\HidePrice\Model
 */
class Management implements ManagementInterface
{
    /**
     * @var HelperLabel
     */
    protected $helperLabel;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var CartHidePrice
     */
    protected $cartHidePrice;

    /**
     * Management constructor.
     *
     * @param HelperLabel $helperLabel
     * @param HelperData $helperData
     * @param CartRepositoryInterface $cartRepository
     * @param CartHidePrice $cartHidePrice
     */
    public function __construct(
        HelperLabel $helperLabel,
        HelperData $helperData,
        CartRepositoryInterface $cartRepository,
        CartHidePrice $cartHidePrice
    ) {
        $this->helperLabel = $helperLabel;
        $this->helperData = $helperData;
        $this->cartRepository = $cartRepository;
        $this->cartHidePrice = $cartHidePrice;
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfig($storeId = null)
    {
        $valueCategories = $this->helperData->getHidePriceCategories($storeId);
        $valueCustomers = $this->helperData->getHidePriceCustomers($storeId);
        $valueAction = $this->helperData->getHidePriceAction($storeId);
        return [
            "configs" => [
                "enable" => (bool) $this->helperData->isEnable($storeId),
                "selector" => $this->helperData->getSelector($storeId),
                "disable_checkout"  => $this->helperData->getDisableCheckout($storeId),
                "action" => [
                    "value" => $valueAction,
                    "label" => $this->helperLabel->getLabelAction($valueAction)
                ],
                "message" => $this->helperData->getHidePriceTextGlobal($storeId),
                "categories" => [
                    "code_categories" => $valueCategories,
                    "label_categories" => $this->helperLabel->getLabelCategories($valueCategories)
                ],
                "customers" => [
                    "value" => $valueCustomers,
                    "label" => $this->helperLabel->getLabelCustomers($valueCustomers)
                ],
                "url" => $this->helperData->getHidePriceUrlConfig($storeId)
            ]
        ];
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function canPlaceOrder($cartId)
    {
        $cart = $this->cartRepository->getActive($cartId);
        if ($this->cartHidePrice->isPlaceOrder($cart)) {
            return [
              "can_place_order" => true
            ];
        }
        return [
            "can_place_order" => false
        ];
    }
}
