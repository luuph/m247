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

namespace Mageplaza\AffiliateUltimate\Model;

use Mageplaza\AffiliateUltimate\Api\OrderRepositoryInterface;
use Mageplaza\AffiliateUltimate\Model\AffiliateFactory;
use Mageplaza\AffiliateUltimate\Model\AffiliateItemFactory;

/**
 * Class OrderRepository
 * @package Mageplaza\AffiliateUltimate\Model
 */
class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var AffiliateFactory
     */
    protected $affiliateFactory;

    /**
     * @var \Mageplaza\AffiliateUltimate\Model\AffiliateFactory
     */
    protected $affiliateItemFactory;

    /**
     * OrderRepository constructor.
     *
     * @param \Mageplaza\AffiliateUltimate\Model\AffiliateFactory $affiliateFactory
     * @param \Mageplaza\AffiliateUltimate\Model\AffiliateItemFactory $affiliateItemFactory
     */
    public function __construct(
        AffiliateFactory $affiliateFactory,
        AffiliateItemFactory $affiliateItemFactory
    ) {
        $this->affiliateFactory = $affiliateFactory;
        $this->affiliateItemFactory = $affiliateItemFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function get($orderId)
    {
        return $this->affiliateFactory->create()->load($orderId);
    }

    /**
     * {@inheritDoc}
     */
    public function getItemById($itemId)
    {
        return $this->affiliateItemFactory->create()->load($itemId);
    }
}
