<?php

namespace Biztech\Translator\Model\ResourceModel\Logcron;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Biztech\Translator\Model\Logcron', 'Biztech\Translator\Model\ResourceModel\Logcron');
    }
}
