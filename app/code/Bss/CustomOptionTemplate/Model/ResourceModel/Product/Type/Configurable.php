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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\CustomOptionTemplate\Model\ResourceModel\Product\Type;

class Configurable extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct.
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
     * Init resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_super_link', 'link_id');
    }

    /**
     * Retrieve parent ids array by required child.
     *
     * @param array $childId
     * @return array
     */
    public function getParentIdsByChild($childId)
    {
        try {
            if (!$childId) {
                return [];
            }

            $select = $this->getConnection()
                ->select()
                ->from(['l' => $this->getMainTable()], ['l.product_id', 'e.entity_id'])
                ->join(
                    ['e' => $this->getTable('catalog_product_entity')],
                    'e.entity_id = l.parent_id',
                    ['e.entity_id']
                )->where('l.product_id IN(?)', $childId, \Zend_Db::INT_TYPE);

            $data = $this->getConnection()->query($select)->fetchAll();
            $parentIds = [];
            foreach ($data as $item) {
                $parentIds[$item['product_id']] = $item['entity_id'];
            }
            return $parentIds;
        } catch (\Exception $e) {
            return [];
        }
    }
}
