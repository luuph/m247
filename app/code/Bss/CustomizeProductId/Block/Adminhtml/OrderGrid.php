<?php

/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomizeProductId
 * @author     Extension Team
 * @copyright  Copyright (c) 2025 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomizeProductId\Block\Adminhtml;

class OrderGrid extends \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid
{
    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->getCollection();

        if ($collection) {
            $collection->addAttributeToSelect('product_id');
        }
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'search_product_id_sku',
            [
                'header' => __('Search by Product ID, SKU'),
                'sortable' => true,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'index' => 'search_product_id_sku'
            ]
        );
        $this->addColumn(
            'product_id',
            [
                'header' => __('Product ID'),
                'sortable' => true,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'index' => 'product_id'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Add column filter to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'search_product_id_sku') {
            $value = $column->getFilter()->getValue();
            $attribute = [
                ['attribute' => 'product_id', 'like' => "%$value%"],
                ['attribute' => 'sku', 'like' => "%$value%"]
            ];
            $condition = $column->getFilter()->getCondition();
            if (!empty($value)) {
                $this->getCollection()->addFieldToFilter($attribute, $condition);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
}
