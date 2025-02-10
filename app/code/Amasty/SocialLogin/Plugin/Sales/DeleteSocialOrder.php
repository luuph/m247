<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Plugin\Sales;

use Amasty\SocialLogin\Api\Data\SalesInterface;
use Amasty\SocialLogin\Model\ResourceModel\Sales as SocialSales;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;

/**
 * Delete related with order entity.
 *
 * Foreign key cannot be added for order.
 * Because declaration of order ID key differs from v2.3 to v2.4.
 * Foreign key with order can not be added due Split Database support.
 */
class DeleteSocialOrder
{
    /**
     * @var SocialSales
     */
    private $socialSales;

    public function __construct(SocialSales $socialSales)
    {
        $this->socialSales = $socialSales;
    }

    /**
     * Delete related with order entity.
     *
     * @see OrderRepository::delete target method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDelete(OrderRepository $subject, OrderInterface $entity): void
    {
        $connection = $this->socialSales->getConnection();

        $where = sprintf('%s = ?', SalesInterface::ORDER_ID);
        $connection->delete($this->socialSales->getMainTable(), [$where => $entity->getId()]);
    }
}
