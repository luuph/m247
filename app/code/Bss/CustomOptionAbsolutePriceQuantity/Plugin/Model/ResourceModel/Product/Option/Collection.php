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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Plugin\Model\ResourceModel\Product\Option;

class Collection
{
    /**
     * After add title to result add description.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $result
     * @param int $storeId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAddTitleToResult(\Magento\Catalog\Model\ResourceModel\Product\Option\Collection $subject, $result, $storeId)
    {
        $connection = $result->getConnection();

        if ($connection->isTableExists($result->getTable('bss_custom_option_description_type'))) {
            $this->addDescriptionTypeToResult($connection, $result, $storeId);
        }

        if ($connection->isTableExists($result->getTable('bss_custom_option_description'))) {
            $this->addDescriptionToResult($connection, $result, $storeId);
        }

        return $result;
    }

    /**
     * Add description type to sql data.
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $result
     * @param int $storeId
     * @return void
     */
    public function addDescriptionTypeToResult($connection, $result, $storeId)
    {
        $productOptionDescriptionTypeTable = $result->getTable('bss_custom_option_description_type');
        $descriptionTypeExpr = $connection->getCheckSql(
            'store_option_bss_description_option_type.bss_description_option_type IS NULL AND store_option_bss_description_option_type.option_id IS NULL',
            'default_option_bss_description_option_type.bss_description_option_type',
            'store_option_bss_description_option_type.bss_description_option_type'
        );

        $result->getSelect()->joinLeft(
            ['default_option_bss_description_option_type' => $productOptionDescriptionTypeTable],
            'default_option_bss_description_option_type.option_id = main_table.option_id OR default_option_bss_description_option_type.option_id IS NULL',
            ['default_bss_description_option_type' => 'bss_description_option_type']
        )->joinLeft(
            ['store_option_bss_description_option_type' => $productOptionDescriptionTypeTable],
            'store_option_bss_description_option_type.option_id = main_table.option_id AND ' . $connection->quoteInto(
                'store_option_bss_description_option_type.store_id = ?',
                $storeId
            ) . ' OR store_option_bss_description_option_type.option_id IS NULL',
            ['store_bss_description_option_type' => 'bss_description_option_type', 'store_bss_description_option_type_option_id' => 'option_id', 'bss_description_option_type' => $descriptionTypeExpr]
        )->where(
            'default_option_bss_description_option_type.store_id = ? OR default_option_bss_description_option_type.store_id IS NULL',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
    }

    /**
     * Add description to sql data.
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $result
     * @param int $storeId
     * @return void
     */
    public function addDescriptionToResult($connection, $result, $storeId)
    {
        $productOptionDescriptionTable = $result->getTable('bss_custom_option_description');
        $descriptionExpr = $connection->getCheckSql(
            'store_option_bss_description_option.bss_description_option IS NULL AND store_option_bss_description_option.option_id IS NULL',
            'default_option_bss_description_option.bss_description_option',
            'store_option_bss_description_option.bss_description_option'
        );

        $result->getSelect()->joinLeft(
            ['default_option_bss_description_option' => $productOptionDescriptionTable],
            'default_option_bss_description_option.option_id = main_table.option_id OR default_option_bss_description_option.option_id IS NULL',
            ['default_bss_description_option' => 'bss_description_option']
        )->joinLeft(
            ['store_option_bss_description_option' => $productOptionDescriptionTable],
            'store_option_bss_description_option.option_id = main_table.option_id AND ' . $connection->quoteInto(
                'store_option_bss_description_option.store_id = ?',
                $storeId
            ) . ' OR store_option_bss_description_option.option_id IS NULL',
            ['store_bss_description_option' => 'bss_description_option', 'store_bss_description_option_option_id' => 'option_id', 'bss_description_option' => $descriptionExpr]
        )->where(
            'default_option_bss_description_option.store_id = ? OR default_option_bss_description_option.store_id IS NULL',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
    }
}
