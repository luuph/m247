<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Plugin\Sales\ResourceModel;

use Amasty\SocialLogin\Api\Data\SalesInterface;
use Amasty\SocialLogin\Model\ResourceModel\Sales;
use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult as GridSearchResult;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;

/**
 * Add Data, Filters, Sorting functional for Order/Shipment Adminhtml Grids for Social Login fields
 */
class ManageGridFields
{
    /**
     * key - grid column name
     * value - sql column name
     */
    public const GRID_COLUMNS = [
        'amasty_sociallogin_code' => 'amsociallogin.' . SalesInterface::TYPE,
    ];

    public const MAIN_TABLE_COLUMNS = ['entity_id', 'order_id'];

    /**
     * @var Sales
     */
    private $resource;

    public function __construct(
        Sales $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Join social login table to grid table
     *
     * @see \Magento\Sales\Model\ResourceModel\Order\Grid\Collection::getSelect
     */
    public function afterGetSelect(GridSearchResult $collection, ?Select $select): ?Select
    {
        if ($select && !\array_key_exists('amsociallogin', $select->getPart(Select::FROM))) {
            // Shipment grid
            $orderIdKey = 'order_id';
            if ($collection instanceof OrderGridCollection) {
                // Order grid
                $orderIdKey = 'entity_id';
            }
            $select->joinLeft(
                ['amsociallogin' => $this->resource->getMainTable()],
                'main_table.' . $orderIdKey . ' = amsociallogin.' . SalesInterface::ORDER_ID,
                self::GRID_COLUMNS
            );
        }

        return $select;
    }

    /**
     * Prepare fields condition and value for filter
     *
     * @see \Magento\Sales\Model\ResourceModel\Order\Grid\Collection::addFieldToFilter
     * @param GridSearchResult $collection
     * @param string|array $field
     * @param null|string|array $condition
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddFieldToFilter(GridSearchResult $collection, $field, $condition = null): array
    {
        if (\is_array($field)) {
            foreach ($field as $key => $fieldItem) {
                $field[$key] = $this->mapField($fieldItem);
            }
        } elseif (\is_string($field)) {
            $field = $this->mapField($field);
        }

        return [$field, $condition];
    }

    private function mapField(string $field): string
    {
        if (isset(self::GRID_COLUMNS[$field])) {
            $field = self::GRID_COLUMNS[$field];
        } elseif (strpos($field, '.') === false && in_array($field, self::MAIN_TABLE_COLUMNS)) {
            $field = 'main_table.' . $field;
        }

        return $field;
    }
}
