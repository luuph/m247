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
     * Prepare collection to be displayed in the grid
     *
     * @return $this
     */
    protected function _prepareCollection()
    {

        $attributes = $this->_catalogConfig->getProductAttributes();
        $store = $this->getStore();

        /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->productCollectionProvider->getCollectionForStore($store);
        $collection->addAttributeToSelect(
            $attributes
        );
        $collection->addAttributeToFilter(
            'type_id',
            $this->_salesConfig->getAvailableProductTypes()
        );

        $this->setCollection($collection);
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
}