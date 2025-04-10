<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Model\ResourceModel\Walletcreditamount;

use \Webkul\Walletsystem\Model\ResourceModel\AbstractCollection;

/**
 * Webkul Walletsystem Class
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\Walletsystem\Model\Walletcreditamount::class,
            \Webkul\Walletsystem\Model\ResourceModel\Walletcreditamount::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

    /**
     * Add store filter
     *
     * @param object $store
     * @param boolean $withAdmin
     * @return this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}
