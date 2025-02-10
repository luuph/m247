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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Model;

class CompatibleWithCOAPriceQty
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get Bss Coap Qty
     * Compatible with COAPriceQty
     *
     * @return bool
     */
    public function getBssCoapQty($templateOptionId)
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            if ($connection->isTableExists('catalog_product_option')) {
                $sql = sprintf("Select main_table.bss_coap_qty from catalog_product_option as main_table where template_option_id = %s", $templateOptionId);
                $result = $connection->fetchAll($sql);
                if ($result[0]['bss_coap_qty']) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $exception){
            return false;
        }
    }

    /**
     * Check and set option
     * Compatible with COAPriceQty
     * Use to DescriptionType, Description, TierPriceOption type is not Select
     * With TierPrice Options Select type use function checkAndSetTierPriceOptionSelect
     *
     * @param array $value
     * @param string $tableName
     * @param string $field
     * @return void
     */
    public function checkAndSetValueOption(&$value, $tableName, $field)
    {
        $templateOptionId = $value['option_id'] ?? $value['id'];
        try {
            $connection = $this->resourceConnection->getConnection();
            if ($connection->isTableExists($tableName)) {
                $sql = $this->prepareSqlQuery($tableName, $templateOptionId);
                $sqlResult = $connection->fetchAll($sql);
                if (isset($sqlResult[0][$field])) {
                    $result = $sqlResult[0][$field];
                    if ($field == "tier_price") {
                        $field = "bss_tier_price_option";
                    }
                    $value[$field] = $result;
                }
            }
        } catch (\Exception $exception) {
            //Skip
        }
    }

    /**
     * Check and set AbsolutePrice not option type select
     * Compatible with COAPriceQty
     *
     * @param object|\Bss\CustomOptionTemplate\Model\Option $option
     * @return void
     */
    public function checkAndSetAbsolutePriceOption($option)
    {
        $templateOptionId = $option->getData('option_id');
        try {
            $connection = $this->resourceConnection->getConnection();
            if ($connection->isTableExists('catalog_product_option_price')) {
                $sql = $this->prepareSqlQuery('catalog_product_option_price', $templateOptionId);
                $sqlResult = $connection->fetchAll($sql);
                if (isset($sqlResult[0]['price']) && isset($sqlResult['0']['price_type'])) {
                    $option->setData('price', $sqlResult[0]['price']);
                    $option->setData('price_type', $sqlResult['0']['price_type']);
                }
            }
        } catch (\Exception $exception) {
            //Skip
        }
    }

    /**
     * Prepare sql query
     *
     * @param string $tableName
     * @param string|int $value
     * @return string
     */
    public function prepareSqlQuery($tableName, $value)
    {
        return sprintf("Select main_table.*, catalog_po.template_option_id
                            from %s as main_table
                            left join catalog_product_option as catalog_po
                                on main_table.option_id = catalog_po.option_id
                            where template_option_id = %s",
                        $tableName,
                        $value
                    );
    }

    /**
     * Compatible with CustomOptionAbsoluteProductQuantity option type select
     *
     * @param object|\Bss\CustomOptionTemplate\Model\Option\Value $_value
     * @return void
     */
    public function checkAndSetTierPriceOptionSelect($_value)
    {
        $optionTypeId = $_value->getId();
        try {
            $connection = $this->resourceConnection->getConnection();
            if ($connection->isTableExists('bss_tier_price_product_option_type_value')) {
                $sql = $this->prepareSqlQueryWithOptionSelect('bss_tier_price_product_option_type_value', $optionTypeId);
                $result = $connection->fetchAll($sql);
                if (isset($result[0]['tier_price'])) {
                    $jsonTierPrice = $result[0]['tier_price'];
                    $_value->setData('bss_tier_price_option', $jsonTierPrice);
                }
            }
        } catch (\Exception $exception) {
            //skip
        }
    }

    /**
     * Check and set absolute price, pricetype to option type select
     * Compatible with COAPriceQty
     *
     * @param object|\Bss\CustomOptionTemplate\Model\Option\Value $_value
     * @return void
     */
    public function checkAndSetAbsolutePriceOptionSelect($_value)
    {
        $templateOptionTypeId = $_value->getData('option_type_id');
        try {
            $connection = $this->resourceConnection->getConnection();
            if ($connection->isTableExists('catalog_product_option_type_price')) {
                $sql = $this->prepareSqlQueryWithOptionSelect('catalog_product_option_type_price', $templateOptionTypeId);
                $sqlResult = $connection->fetchAll($sql);
                if (isset($sqlResult[0]['price']) && isset($sqlResult['0']['price_type'])) {
                    $_value->setData('price', $sqlResult[0]['price']);
                    $_value->setData('price_type', $sqlResult['0']['price_type']);
                }
            }
        } catch (\Exception $exception) {
            //Skip
        }
    }

    /**
     * Prepare sql query with option select
     * Compatible with COAPriceQty
     *
     * @param string $tableName
     * @param int|string $value
     * @return string
     */
    public function prepareSqlQueryWithOptionSelect($tableName, $value)
    {
        return sprintf("Select main_table.*, catalog_potv.template_option_type_id
                from %s as main_table
                left join catalog_product_option_type_value as catalog_potv
                    on main_table.option_type_id = catalog_potv.option_type_id
                where template_option_type_id = %s",
            $tableName,
            $value
        );
    }
}
