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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Options extends AbstractDb
{
    const CUSTOMER_APPROVAL_STATUS = "activasion_status";

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('eav_attribute_option_value', 'value_id');
    }

    /**
     * Get Option Id by value
     *
     * @param $value
     * @return string|null
     */
    public function getStatusValue($value)
    {
        $attributeId = $this->getCustomerApprovalAttributeId();
        if ($attributeId) {
            $connection = $this->getConnection();
            $query = $connection->select()
                ->from(
                    ['eav_value' => $connection->getTableName('eav_attribute_option_value')],
                    ['option_id']
                )
                ->join(
                    ['eav_option' => $connection->getTableName('eav_attribute_option')],
                    'eav_value.option_id = eav_option.option_id',
                    []
                )
                ->where('eav_option.attribute_id = ?', $attributeId)
                ->where('eav_value.value = ?', $value);

            $optionId = $connection->fetchOne($query);
            if ($optionId) {
                return $optionId;
            }
        }
        return null;
    }

    /**
     * Get Customer Approval AttributeId
     *
     * @return string
     */
    public function getCustomerApprovalAttributeId() {
        $connection = $this->getConnection();
        $select = $connection->select('')
            ->from(
                $connection->getTableName('eav_attribute'),
                ['attribute_id']
            )->where('attribute_code = ?', self::CUSTOMER_APPROVAL_STATUS);
        return $connection->fetchOne($select);
    }
}
