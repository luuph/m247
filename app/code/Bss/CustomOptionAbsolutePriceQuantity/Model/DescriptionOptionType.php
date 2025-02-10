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
namespace Bss\CustomOptionAbsolutePriceQuantity\Model;

use Magento\Store\Model\Store;

class DescriptionOptionType extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * construct
     */
    public function _construct()
    {
        $this->_init('bss_custom_option_description_type', 'id');
    }

    /**
     * Save description type.
     *
     * @param \Magento\Catalog\Model\Product\Option $result
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function saveDescriptionType($result)
    {
        $connection = $this->getConnection();
        $titleTableName = $this->getTable('bss_custom_option_description_type');
        foreach ([Store::DEFAULT_STORE_ID, $result->getStoreId()] as $storeId) {
            $existInCurrentStore = $this->getColFromOptionTable($titleTableName, (int)$result->getId(), (int)$storeId);
            $existInDefaultStore = (int)$storeId == Store::DEFAULT_STORE_ID ?
                $existInCurrentStore :
                $this->getColFromOptionTable(
                    $titleTableName,
                    (int)$result->getId(),
                    Store::DEFAULT_STORE_ID
                );

            if ($result->getBssDescriptionOptionType() !== '') {
                $isDeleteStoreTitle = (bool)$result->getData('is_delete_store_bss_description_option_type');
                if ($existInCurrentStore) {
                    if ($isDeleteStoreTitle && (int)$storeId != Store::DEFAULT_STORE_ID) {
                        $connection->delete($titleTableName, ['id = ?' => $existInCurrentStore]);
                    } elseif ($result->getStoreId() == $storeId) {
                        $data = $this->_prepareDataForTable(
                            new \Magento\Framework\DataObject(['bss_description_option_type' => $result->getBssDescriptionOptionType()]),
                            $titleTableName
                        );
                        $connection->update(
                            $titleTableName,
                            $data,
                            [
                                'option_id = ?' => $result->getId(),
                                'store_id  = ?' => $storeId,
                            ]
                        );
                    }
                } else {
                    // we should insert record into not default store only of if it does not exist in default store
                    if (($storeId == Store::DEFAULT_STORE_ID && !$existInDefaultStore) ||
                        (
                            $storeId != Store::DEFAULT_STORE_ID &&
                            !$existInCurrentStore &&
                            !$isDeleteStoreTitle
                        )
                    ) {
                        $data = $this->_prepareDataForTable(
                            new \Magento\Framework\DataObject(
                                [
                                    'option_id' => $result->getId(),
                                    'store_id' => $storeId,
                                    'bss_description_option_type' => $result->getBssDescriptionOptionType(),
                                ]
                            ),
                            $titleTableName
                        );
                        $connection->insert($titleTableName, $data);
                    }
                }
            } else {
                if ($result->getId() && $result->getStoreId() > Store::DEFAULT_STORE_ID
                    && $storeId
                ) {
                    $connection->delete(
                        $titleTableName,
                        [
                            'option_id = ?' => $result->getId(),
                            'store_id  = ?' => $result->getStoreId(),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Get first col from first row for option table
     *
     * @param string $tableName
     * @param int $optionId
     * @param int $storeId
     * @return string
     */
    protected function getColFromOptionTable($tableName, $optionId, $storeId)
    {
        $connection = $this->getConnection();
        $statement = $connection->select()->from(
            $tableName
        )->where(
            'option_id = ?',
            $optionId
        )->where(
            'store_id  = ?',
            $storeId
        );

        return $connection->fetchOne($statement);
    }
}
