<?php

namespace Biztech\Translator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Crondata resource
 */
class Crondata extends AbstractDb
{

    protected $_date;

    public function __construct(
        Context $context,
        DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('translator_cron', 'id');
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setCronDate($this->_date->gmtDate());
        }
        return parent::_beforeSave($object);
    }
}
