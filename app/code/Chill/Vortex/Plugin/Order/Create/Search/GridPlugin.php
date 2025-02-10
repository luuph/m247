<?php

namespace Chill\Vortex\Plugin\Order\Create\Search;

use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid;
use Magento\Framework\DataObject;

class GridPlugin extends Grid
{

   protected function _prepareColumns()
   {
       $this->addColumn('product_id', ['header' => __('Product ID'), 'index' => 'hideprice_action']);
       return parent::_prepareColumns();
   }
}
