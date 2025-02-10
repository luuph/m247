<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Model;

use Amasty\SocialLogin\Model\Repository\SalesRepository;
use Amasty\SocialLogin\Model\ResourceModel\Social as SocialResource;
use Amasty\SocialLogin\Model\SalesFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order;

class CreateSalesItem
{
    /**
     * @var SalesFactory
     */
    private $salesFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var SocialResource
     */
    private $socialResource;

    /**
     * @var Repository\SalesRepository
     */
    private $repository;

    public function __construct(
        SalesFactory $salesFactory,
        PriceCurrencyInterface $priceCurrency,
        SocialResource $socialResource,
        SalesRepository $repository
    ) {
        $this->salesFactory = $salesFactory;
        $this->priceCurrency = $priceCurrency;
        $this->socialResource = $socialResource;
        $this->repository = $repository;
    }

    /**
     * Save social analytics data.
     *
     * @param Order $order
     * @param string $userProfile
     */
    public function createByOrder(Order $order, $userProfile): void
    {
        /** @var Sales $sales */
        $sales = $this->salesFactory->create();

        $total = $this->priceCurrency->convert(
            $order->getBaseGrandTotal(),
            $order->getStore(),
            $order->getBaseCurrency()
        );

        $sales->setSocialId($userProfile);
        $sales->setAmount($total);
        $sales->setItems($order->getTotalQtyOrdered());
        $sales->setType($this->socialResource->getTypeBySocialId($userProfile));
        $sales->setOrderId((int) $order->getId());

        $this->repository->save($sales);
    }
}
