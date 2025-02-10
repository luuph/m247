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
namespace Bss\CustomOptionTemplate\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Bss\CustomOptionTemplate\Helper\HelperModelTemplate;

class Title extends AbstractDb
{
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var HelperModelTemplate
     */
    protected $helperModelTemplate;

    /**
     * Title constructor.
     * @param Context $context
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param HelperModelTemplate $helperModelTemplate
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Bss\CustomOptionTemplate\Helper\HelperModelTemplate $helperModelTemplate,
        $connectionName = null
    ) {
        $this->helperModelTemplate = $helperModelTemplate;
        $this->storeRepository = $storeRepository;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_option_title', 'option_title_id');
    }

    /**
     * Delete titles
     *
     * @param int $optionId
     * @return $this
     */
    public function deleteOptionTitles($optionId)
    {
        $this->getConnection()->delete(
            $this->getTable('catalog_product_option_title'),
            ['option_id = ?' => $optionId, 'store_id NOT IN (?)' => 0]
        );

        return $this;
    }

    /**
     * Delete Opion value titles
     *
     * @param int $optionTypeId
     * @return $this
     */
    public function deleteOptionValueTitles($optionTypeId)
    {
        $this->getConnection()->delete(
            $this->getTable('catalog_product_option_type_title'),
            ['option_type_id = ?' => $optionTypeId, 'store_id NOT IN (?)' => 0]
        );

        return $this;
    }

    /**
     * @param array $customDataOptions
     * @param string $type
     * @return $this
     */
    public function addVisibleOptions($customDataOptions, $type = 'customer')
    {
        if (!empty($customDataOptions)) {
            $listOptionIds =[];
            foreach ($customDataOptions as $customDataOption) {
                $listOptionIds[] = $customDataOption['option_id'];
            }
            $tableName = 'bss_visible_custom_option_group_customer';
            if ($type == 'store') {
                $tableName = 'bss_visible_custom_option_storeview';
            }

            $this->getConnection()->delete(
                $this->getTable($tableName),
                ['option_id IN (?)' => $listOptionIds]
            );
            $this->getConnection()->insertMultiple(
                $this->getTable($tableName),
                $customDataOptions
            );
        }
        return $this;
    }

    /**
     * @param array $customDataValues
     * @return $this
     */
    public function addIsDefaultValue($customDataValues)
    {
        if (!empty($customDataValues)) {
            $listOptionIds =[];
            foreach ($customDataValues as $customDataValue) {
                $listOptionIds[] = $customDataValue['option_type_id'];
            }
            $tableName = 'bss_custom_option_value_default';

            $this->getConnection()->delete(
                $this->getTable($tableName),
                ['option_type_id IN (?)' => $listOptionIds]
            );
            $this->getConnection()->insertMultiple(
                $this->getTable($tableName),
                $customDataValues
            );
        }
        return $this;
    }

    /**
     * @param array $titleData
     * @param string $typeId
     * @return $this
     */
    public function addTitleForStores($titleData, $typeId = 'option_id')
    {
        $storeIds = [];
        foreach ($this->storeRepository->getList() as $store) {
            if ($store->getId() != 0) {
                $storeIds[] = $store->getId();
            }
        }
        if (!empty($titleData)) {
            $listOptionTitleData =[];
            $tableName = 'catalog_product_option_title';
            if ($typeId == 'option_type_id') {
                $tableName = 'catalog_product_option_type_title';
            }
            foreach ($titleData as $id => $datum) {
                $this->deleteTitleForStores($tableName, $typeId, $id, $storeIds);
                foreach ($storeIds as $storeId) {
                    if (isset($datum[$storeId])) {
                        $listOptionTitleData[] = [
                            $typeId => $id,
                            'store_id' => $storeId,
                            'title' => $datum[$storeId]
                        ];
                    }
                }
            }
            if (!empty($listOptionTitleData)) {
                $this->getConnection()->insertMultiple(
                    $this->getTable($tableName),
                    $listOptionTitleData
                );
            }
        }
        return $this;
    }

    /***
     * @param string $tableName
     * @param int $typeId
     * @param int $id
     * @param array $storeIds
     */
    protected function deleteTitleForStores($tableName, $typeId, $id, $storeIds)
    {
        $this->getConnection()->delete(
            $this->getTable($tableName),
            [
                $typeId . ' = ?' => $id,
                'store_id IN (?)' => $storeIds
            ]
        );
    }

    /**
     * @param int $productId
     * @param array $checkOptionAndRequire
     * @return $this
     */
    public function addHasOptionAndRequire($productId, $checkOptionAndRequire)
    {
        $column = $this->helperModelTemplate->getColumn();
        $this->getConnection()->update(
            $this->getTable('catalog_product_entity'),
            $checkOptionAndRequire,
            [$column . '=? ' => $productId]
        );
        return $this;
    }
}
