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
 * @category  BSS
 * @package   Bss_ProductTags
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Model\ResourceModel\ProTags;

use Bss\ProductTags\Model\ResourceModel\AbstractCollection;

/**
 * Class Collection
 *
 * @package Bss\ProductTags\Model\ResourceModel\ProTags
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     * @codingStandardsIgnoreStart
     */
    protected $_idFieldName = 'protags_id';

    /**
     * Init resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Bss\ProductTags\Model\Protags', 'Bss\ProductTags\Model\ResourceModel\Protags');
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * @return AbstractCollection
     */
    public function _afterLoad()
    {
        $this->performAfterLoad('bss_protags_rule_store', 'protags_id', 'store_id');
        $this->performAfterLoad('bss_protags_tag', 'protags_id', 'name_tag');
        return parent::_afterLoad();
    }

    /**
     * @return int|void
     */
    public function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('bss_protags_rule_store', 'protags_id');
    }
}
