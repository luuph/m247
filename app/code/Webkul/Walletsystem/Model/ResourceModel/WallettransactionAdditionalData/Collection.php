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

namespace Webkul\Walletsystem\Model\ResourceModel\WallettransactionAdditionalData;

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
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\Walletsystem\Model\WallettransactionAdditionalData::class,
            \Webkul\Walletsystem\Model\ResourceModel\WallettransactionAdditionalData::class
        );
    }
}
