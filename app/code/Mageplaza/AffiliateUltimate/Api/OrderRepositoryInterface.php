<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\AffiliateUltimate\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\AffiliateUltimate\Api\Data\AffiliateInterface;
use Mageplaza\AffiliateUltimate\Api\Data\AffiliateItemInterface;

/**
 * Interface OrderRepositoryInterface
 * @api
 */
interface OrderRepositoryInterface
{
    /**
     * @param int $orderId The order ID.
     *
     * @return AffiliateInterface Affiliate.
     * @throws NoSuchEntityException
     */
    public function get($orderId);

    /**
     * @param int $itemId The item ID.
     *
     * @return AffiliateItemInterface Affiliate item.
     * @throws NoSuchEntityException
     */
    public function getItemById($itemId);
}
