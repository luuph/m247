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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Model\ResourceModel\PriceAlert;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define price alert collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Bss\ProductStockAlert\Model\PriceAlert::class,
            \Bss\ProductStockAlert\Model\ResourceModel\PriceAlert::class
        );
    }

    /**
     * Select price alert group by product id
     *
     * @return $this
     */
    public function groupByProductId()
    {
        $this->getSelect()->group('product_id');
        return $this;
    }
}
