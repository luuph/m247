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
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliateUltimate\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemExtension;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Mageplaza\AffiliateUltimate\Api\Data\AffiliateInterface;
use Mageplaza\AffiliateUltimate\Api\Data\AffiliateItemInterface;

/**
 * Class OrderGet
 * @package Mageplaza\AffiliateUltimate\Plugin
 */
class OrderGet
{
    /**
     * @var \Mageplaza\AffiliateUltimate\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /** @var OrderExtensionFactory */
    protected $orderExtensionFactory;

    /** @var OrderItemExtensionFactory */
    protected $orderItemExtensionFactory;

    /**
     * OrderGet constructor.
     *
     * @param \Mageplaza\AffiliateUltimate\Api\OrderRepositoryInterface $orderRepository
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param OrderItemExtensionFactory $orderItemExtensionFactory
     */
    public function __construct(
        \Mageplaza\AffiliateUltimate\Api\OrderRepositoryInterface $orderRepository,
        OrderExtensionFactory $orderExtensionFactory,
        OrderItemExtensionFactory $orderItemExtensionFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->orderItemExtensionFactory = $orderItemExtensionFactory;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $resultOrder
     *
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $resultOrder
    ) {
        $resultOrder = $this->getOrderAffiliate($resultOrder);
        $resultOrder = $this->getOrderItemAffiliate($resultOrder);

        return $resultOrder;
    }

    /**
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    protected function getOrderAffiliate(OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getMpAffiliate()) {
            return $order;
        }

        try {
            /** @var AffiliateInterface $affiliateData */
            $affiliateData = $this->orderRepository->get($order->getEntityId());
        } catch (NoSuchEntityException $e) {
            return $order;
        }

        /** @var OrderExtension $orderExtension */
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        $orderExtension->setMpAffiliate($affiliateData);
        $order->setExtensionAttributes($orderExtension);

        return $order;
    }

    /**
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    protected function getOrderItemAffiliate(OrderInterface $order)
    {
        $orderItems = $order->getItems();
        if (null !== $orderItems) {
            /** @var OrderItemInterface $orderItem */
            foreach ($orderItems as $orderItem) {
                $extensionAttributes = $orderItem->getExtensionAttributes();
                if ($extensionAttributes && $extensionAttributes->getMpAffiliate()) {
                    continue;
                }

                try {
                    /** @var AffiliateItemInterface $affiliateData */
                    $affiliateData = $this->orderRepository->getItemById($orderItem->getItemId());
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                /** @var OrderItemExtension $orderItemExtension */
                $orderItemExtension = $extensionAttributes
                    ? $extensionAttributes
                    : $this->orderItemExtensionFactory->create();

                $orderItemExtension->setMpAffiliate($affiliateData);
                $orderItem->setExtensionAttributes($orderItemExtension);
            }
        }

        return $order;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param Collection $resultOrder
     *
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        Collection $resultOrder
    ) {
        /** @var  $order */
        foreach ($resultOrder->getItems() as $order) {
            $this->afterGet($subject, $order);
        }

        return $resultOrder;
    }
}
