<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Appcreator Model.
 */
class Appcreator extends \Magento\Framework\Model\AbstractModel
{
    protected const CACHE_TAG = "mobikul_appcreator";
    protected const NOROUTE_ID = "no-route";
    
    /**
     * CacheTag Variable
     *
     * @var String
     */
    protected $_cacheTag = "mobikul_appcreator";
    
    /**
     * EventPrefix Variable
     *
     * @var String
     */
    protected $_eventPrefix = "mobikul_appcreator";

    /**
     * Construct function
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\Appcreator::class);
    }
}
