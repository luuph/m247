<?php
namespace MageArray\Popup\Model\ResourceModel;

use Magento\Store\Model\Store;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Popup
 * @package MageArray\Popup\Model\ResourceModel
 */
class Popup extends AbstractDb
{

    /**
     * @var null
     */
    protected $store = null;
    /**
     * @var null
     */
    protected $connection = null;

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('magearray_popup', 'popup_id');
    }

    /**
     * Popup constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }
}
