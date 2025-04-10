<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\ResourceModel;

/**
 * Class Notification resourceModel
 */
class Notification extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store variable
     *
     * @var mixed
     */
    protected $store = null;

    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init("mobikul_notification", "id");
    }

    /**
     * Load function
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return void
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = "identifier";
        }
        return parent::load($object, $value, $field);
    }

    /**
     * SetStore function
     *
     * @param mixed $store
     * @return void
     */
    public function setStore($store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * GetStore function
     *
     * @return void
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->store);
    }
}
