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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TierPriceOptionValue extends AbstractDb
{
    /**
     * construct
     */
    public function _construct()
    {
        $this->_init('bss_tier_price_product_option_type_value', 'id');
    }

    /**
     * @param int $optionTypeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByOptionTyeId($optionTypeId)
    {
        $bind = ['option_type_id' => $optionTypeId];
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'option_type_id = :option_type_id'
        )->limit(1);

        return $this->getConnection()->fetchRow($select, $bind);
    }

    /**
     * @param int $optionTypeId
     * @param string $type
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTierPrice($optionTypeId, $type = 'tier_price')
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                $this->getMainTable(),
                $type
            )->where('option_type_id = ?', $optionTypeId);

        return $this->getConnection()->fetchOne($select);
    }
}
