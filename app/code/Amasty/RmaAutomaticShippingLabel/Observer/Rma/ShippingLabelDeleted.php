<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Observer\Rma;

use Amasty\RmaAutomaticShippingLabel\Model\ShippingLabel\Repository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ShippingLabelDeleted implements ObserverInterface
{
    /**
     * @var Repository
     */
    private $labelRepository;

    public function __construct(
        Repository $labelRepository
    ) {
        $this->labelRepository = $labelRepository;
    }

    public function execute(Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();

        if ($this->labelRepository->isLabelExistsForRequest($request->getRequestId())) {
            $this->labelRepository->delete(
                $this->labelRepository->getByRequestId($request->getRequestId())
            );
        }
    }
}
