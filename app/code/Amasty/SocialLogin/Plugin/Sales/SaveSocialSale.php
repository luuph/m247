<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Plugin\Sales;

use Amasty\SocialLogin\Model\CreateSalesItem;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Service\OrderService;
use Psr\Log\LoggerInterface;

class SaveSocialSale
{
    /**
     * @var CustomerSession
     */
    private $session;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CreateSalesItem
     */
    private $createSalesItem;

    public function __construct(
        CustomerSession $session,
        LoggerInterface $logger,
        CreateSalesItem $createSalesItem
    ) {
        $this->session = $session;
        $this->logger = $logger;
        $this->createSalesItem = $createSalesItem;
    }

    /**
     * @see OrderService::place
     * @param OrderService $subject
     * @param OrderInterface $result
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(OrderService $subject, OrderInterface $result, OrderInterface $order): OrderInterface
    {
        $userProfile = $this->session->getAmSocialIdentifier();
        if ($userProfile && $order->getId()) {
            try {
                $this->createSalesItem->createByOrder($order, $userProfile);
            } catch (\Exception $ex) {
                $this->logger->critical($ex);
            }
        }

        return $result;
    }
}
