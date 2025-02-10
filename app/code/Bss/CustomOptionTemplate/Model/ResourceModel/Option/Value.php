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
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Model\ResourceModel\Option;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Value extends AbstractDb
{
    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_custom_option_template_option_type', 'option_type_id');
    }

    /**
     * @param int $optionId
     * @param int $templateOptionTypeId
     * @return string
     */
    public function getBaseOptionTypeId($optionId, $templateOptionTypeId)
    {
        $bind = ['option_id' => $optionId, 'template_option_type_id' => $templateOptionTypeId];
        $select = $this->getConnection()->select()->from(
            $this->getTable('catalog_product_option_type_value'),
            ['option_type_id']
        )->where(
            'option_id = :option_id'
        )->where(
            'template_option_type_id = :template_option_type_id'
        );
        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * @param int $valueId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkIsDefault($valueId)
    {
        $bind = ['option_type_id' => $valueId];
        $select = $this->getConnection()->select()->from(
            $this->getTable('bss_custom_option_value_default'),
            ['is_default']
        )->where(
            'option_type_id = :option_type_id'
        );
        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * @param int $valueId
     * @param array $data
     * @return $this
     */
    public function addIsDefaultForValue($valueId, $data)
    {
        $data['option_type_id'] = $valueId;
        $tableName = 'bss_custom_option_value_default';

        $this->getConnection()->delete(
            $this->getTable($tableName),
            ['option_type_id =?' => $valueId]
        );
        $this->getConnection()->insert(
            $this->getTable($tableName),
            $data
        );
        return $this;
    }

    /**
     * @param int $valueId
     * @return string
     */
    public function getTitleValue($valueId)
    {
        $bind = ['option_type_id' => $valueId];
        $select = $this->getConnection()->select()->from(
            $this->getTable('bss_custom_option_template_option_type'),
            ['title']
        )->where(
            'option_type_id = :option_type_id'
        );
        return $this->getConnection()->fetchOne($select, $bind);
    }
}
