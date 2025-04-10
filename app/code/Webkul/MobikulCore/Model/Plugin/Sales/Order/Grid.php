<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\Plugin\Sales\Order;
 
 /**
  * Grid Class for order purchase point.
  */
class Grid
{
    /**
     * @var String
     */
    public static $table = 'sales_order_grid';

    /**
     * @var String
     */
    public static $leftJoinTable = 'sales_order';
 
    /**
     * AfterSearch Function
     *
     * @param Mixed $intercepter
     * @param Mixed $collection
     */
    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {
 
            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);
 
            $collection
                ->getSelect()
                ->join(
                    ['co'=>$leftJoinTableName],
                    "co.entity_id = main_table.entity_id",
                    [
                        'purchase_point' => 'co.purchase_point'
                    ]
                );
 
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
 
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);
 
        }
        return $collection;
    }
}
