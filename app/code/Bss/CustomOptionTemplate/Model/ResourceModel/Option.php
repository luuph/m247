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
use \Magento\Framework\Model\ResourceModel\Db\Context;
use Bss\CustomOptionTemplate\Helper\HelperModelTemplate;

class Option extends AbstractDb
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var HelperModelTemplate
     */
    protected $helperModelTemplate;

    /**
     * Option constructor.
     * @param Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param HelperModelTemplate $helperModelTemplate
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Bss\CustomOptionTemplate\Helper\HelperModelTemplate $helperModelTemplate,
        $connectionName = null
    ) {
        $this->logger =  $logger;
        $this->helperModelTemplate = $helperModelTemplate;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_custom_option_template_option', 'option_id');
    }

    /**
     * @param int $productId
     * @param int $templateOptionId
     * @return string
     */
    public function getBaseOptionId($productId, $templateOptionId)
    {
        $bind = ['product_id' => $productId, 'template_option_id' => $templateOptionId];
        $select = $this->getConnection()->select()->from(
            $this->getTable('catalog_product_option'),
            ['option_id']
        )->where(
            'product_id = :product_id'
        )->where(
            'template_option_id = :template_option_id'
        );
        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * @param int $productId
     * @param int $templateId
     */
    public function removeTemplateId($productId, $templateId)
    {
        try {
            $bind = ['attribute_code' => 'tenplates_included'];
            $select = $this->getConnection()->select()->from(
                $this->getTable('eav_attribute'),
                ['attribute_id']
            )->where(
                'attribute_code = :attribute_code'
            );
            $attrTemplateIncludeId = $this->getConnection()->fetchOne($select, $bind);
            $column = $this->helperModelTemplate->getColumn();

            $bind = ['attribute_id' => $attrTemplateIncludeId, $column => $productId];
            $select = $this->getConnection()->select()->from(
                $this->getTable('catalog_product_entity_varchar'),
                ['value_id','value']
            )->where(
                'attribute_id = :attribute_id'
            )->where(
                $column . ' = :' . $column
            );
            $data = $this->getConnection()->fetchRow($select, $bind);
            if (!empty($data)) {
                $templateArr = explode(",", $data['value']);
                if (in_array($templateId, $templateArr)) {
                    unset($templateArr[array_search($templateId, $templateArr)]);
                }
                $templateArr = implode(",", $templateArr);
                $this->getConnection()->update(
                    $this->getTable('catalog_product_entity_varchar'),
                    ['value' => $templateArr],
                    ['value_id =?' => $data['value_id']]
                );
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    /**
     * @param int $optionId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVisibleCustomer($optionId)
    {
        $bind = ['option_id' => $optionId];
        $select = $this->getConnection()->select()->from(
            $this->getTable('bss_visible_custom_option_group_customer'),
            ['visible_for_group_customer']
        )->where(
            'option_id = :option_id'
        );
        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * @param int $optionId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVisibleStore($optionId)
    {
        $bind = ['option_id' => $optionId];
        $select = $this->getConnection()->select()->from(
            $this->getTable('bss_visible_custom_option_storeview'),
            ['visible_for_store_view']
        )->where(
            'option_id = :option_id'
        );
        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * @param int $optionId
     * @param array $visibleData
     * @param string $type
     * @return $this
     */
    public function addVisibleOptions($optionId, $visibleData, $type = 'customer')
    {
        $visibleData['option_id'] = $optionId;
        $tableName = 'bss_visible_custom_option_group_customer';
        if ($type == 'store') {
            $tableName = 'bss_visible_custom_option_storeview';
        }

        $this->getConnection()->delete(
            $this->getTable($tableName),
            ['option_id =?' => $optionId]
        );
        $this->getConnection()->insert(
            $this->getTable($tableName),
            $visibleData
        );
        return $this;
    }

    /**
     * @param int $templateOptionId
     * @return string
     */
    public function getTemplateIdFromTemplateOptionId($templateOptionId)
    {
        $bind = ['option_id' => $templateOptionId];
        $select = $this->getConnection()->select()->from(
            $this->getTable('bss_custom_option_template_option'),
            ['template_id']
        )->where(
            'option_id = :option_id'
        );
        return $this->getConnection()->fetchOne($select, $bind);
    }
}
