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
namespace Bss\ProductStockAlert\Model\ResourceModel;

class PriceAlert extends \Bss\ProductStockAlert\Model\ResourceModel\AbstractResource
{
    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_product_alert_price', 'id');
    }

    /**
     * Join data price alert with final price product
     *
     * @param string $productId
     * @param array $priceAlertIds
     * @return array
     */
    public function getDataPrice($productId, $priceAlertIds = [])
    {
        try {
            $select = $this->getConnection()->select()->from(
                $this->getMainTable()
            )->joinLeft(
                ['tb_price_core' => $this->getTable('catalog_product_index_price')],
                'bss_product_alert_price.product_id = tb_price_core.entity_id
                AND bss_product_alert_price.website_id = tb_price_core.website_id
                AND bss_product_alert_price.customer_group = tb_price_core.customer_group_id',
                ['tb_price_core.final_price', 'tb_price_core.min_price']
            )->where(
                'product_id = ?',
                $productId
            );

            if ($priceAlertIds) {
                $select->where(
                    'bss_product_alert_price.id IN (?)',
                    $priceAlertIds
                );
            }

            return $this->getConnection()->fetchAll($select);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return [];
        }
    }

    /**
     * Update price alert record.
     *
     * @param string|int $priceAlertId
     * @param array $data
     * @return $this|void
     */
    public function updatePriceAlert($priceAlertId, $data)
    {
        try {
            $this->beginTransaction();
            $this->getConnection()->update(
                $this->getConnection()->getTableName($this->getMainTable()),
                $data,
                [
                    'id = ?' => $priceAlertId
                ]
            );
            $this->commit();
        } catch (\Exception $e) {
            return $this;
        }
    }

    public function hasEmail($customerId, $productId, $websiteId)
    {
        try {
            $select = $this->getConnection()->select()->from($this->getMainTable())
                ->where('customer_id = :customer_id')
                ->where('product_id  = :product_id')
                ->where('website_id  = :website_id');
            $bind = [
                ':customer_id' => $customerId,
                ':product_id' => $productId,
                ':website_id' => $websiteId,
            ];
            $data = $this->getConnection()->fetchRow($select, $bind);
            return is_array($data) && count($data);
        } catch (\Exception $e) {
            return false;
        }
    }
}
