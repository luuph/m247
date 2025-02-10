<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Plugin;

use Amasty\Rma\Model\Request\DataProvider\Form;
use Amasty\RmaAutomaticShippingLabel\Model\ShippingLabel\Repository;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Add weight field to return items
 */
class FormDataProvider
{
    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Repository
     */
    private $labelRepository;

    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        Repository $labelRepository,
        LoggerInterface $logger
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->logger = $logger;
        $this->labelRepository = $labelRepository;
    }

    public function afterGetData(Form $subject, array $data): array
    {
        if (!empty($data)) {
            $requestId = (int)$data['items'][0]['request_id'];

            if (isset($data[$requestId]['shipping_label'])) {
                $data[$requestId]['shipping_label'][0]['isGenerated']
                    = $this->labelRepository->isLabelExistsForRequest($requestId);
            }

            foreach ($data[$requestId]['return_items'] as &$returnItem) {
                foreach ($returnItem as &$returnItemData) {
                    try {
                        $orderItem = $this->orderItemRepository->get($returnItemData['order_item_id']);
                        $returnItemData['weight'] = round((float)$orderItem->getWeight(), 2);
                        $returnItemData['product_id'] = $orderItem->getProductId();
                        $returnItemData['customs_value'] = sprintf('%.2F', $orderItem->getPrice());
                    } catch (\Throwable $e) {
                        $this->logger->critical($e);
                    }
                }
            }
        }

        return $data;
    }
}
