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

class Template extends AbstractDb
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
     * Template constructor.
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
        $this->helperModelTemplate = $helperModelTemplate;
        $this->logger =  $logger;
        parent::__construct($context, $connectionName);
    }
    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_custom_option_template', 'template_id');
    }

    /**
     * @param int $productId
     * @param string $attributeCode
     * @return string
     */
    public function getAttribeProductData($productId, $attributeCode)
    {
        $column = $this->helperModelTemplate->getColumn();
        $bind = ['attribute_code' => $attributeCode];
        $select = $this->getConnection()->select()->from(
            $this->getTable('eav_attribute'),
            ['attribute_id']
        )->where(
            'attribute_code = :attribute_code'
        );
        $attributeId = $this->getConnection()->fetchOne($select, $bind);
        $bind = ['attribute_id' => $attributeId, $column => $productId];
        $select = $this->getConnection()->select()->from(
            $this->getTable('catalog_product_entity_varchar'),
            ['value']
        )->where(
            'attribute_id = :attribute_id'
        )->where(
            $column . '= :' . $column
        );
        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * @param string $data
     * @param int $templateId
     */
    public function setOptionTemplateData($data, $templateId)
    {
        try {
            $this->getConnection()->update(
                $this->getMainTable(),
                ['options_data' => $data],
                ['template_id =?' => $templateId]
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }
}
