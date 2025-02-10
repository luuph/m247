<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package RMA Base for Magento 2
 */

namespace Amasty\Rma\ViewModel\Order\History;

use Amasty\Rma\Api\CreateReturnProcessorInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Layout;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;

class Grid implements ArgumentInterface
{
    /**
     * @var CreateReturnProcessorInterface
     */
    private $createReturnProcessor;

    /**
     * @var Layout
     */
    private $layout;

    public function __construct(
        CreateReturnProcessorInterface $createReturnProcessor,
        Layout $layout
    ) {
        $this->createReturnProcessor = $createReturnProcessor;
        $this->layout = $layout;
    }

    public function getReturnableOrderIds(): string
    {
        $orderCollection = $this->layout->getBlock('sales.order.history.pager')->getCollection();

        if (!($orderCollection instanceof Collection)) {
            return '';
        }

        $returnableOrderReadIds = [];

        /** @var Order $order */
        foreach ($orderCollection as $order) {
            $returnOrder = $this->createReturnProcessor->process($order->getId());
            $returnItems = $returnOrder ? $returnOrder->getItems() : [];

            foreach ($returnItems as $returnItem) {
                if ($returnItem->isReturnable()) {
                    $returnableOrderReadIds[] = $order->getRealOrderId() . '-' . $order->getId();
                    continue 2;
                }
            }
        }

        return implode(',', $returnableOrderReadIds);
    }
}
