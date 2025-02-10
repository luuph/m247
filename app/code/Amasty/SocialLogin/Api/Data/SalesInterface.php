<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Api\Data;

interface SalesInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const ENTITY_ID = 'entity_id';

    public const SOCIAL_ID = 'social_id';

    public const ITEMS = 'items';

    public const AMOUNT = 'amount';

    public const ORDER_ID = 'order_id';

    public const TYPE = 'type';

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\SocialLogin\Api\Data\SalesInterface
     */
    public function setEntityId($entityId);

    /**
     * @return string|null
     */
    public function getSocialId();

    /**
     * @param string|null $socialId
     *
     * @return \Amasty\SocialLogin\Api\Data\SalesInterface
     */
    public function setSocialId($socialId);

    /**
     * @return int
     */
    public function getItems();

    /**
     * @param int $items
     *
     * @return \Amasty\SocialLogin\Api\Data\SalesInterface
     */
    public function setItems($items);

    /**
     * @return float
     */
    public function getAmount();

    /**
     * @param float $amount
     *
     * @return \Amasty\SocialLogin\Api\Data\SalesInterface
     */
    public function setAmount($amount);

    /**
     * Get Related Sales Order ID.
     *
     * @return int|null
     */
    public function getOrderId(): ?int;

    /**
     * Set Related Sales Order ID.
     *
     * @param int $amount
     */
    public function setOrderId(int $amount): void;

    /**
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * @param string $type
     */
    public function setType(string $type): void;
}
