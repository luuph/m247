<?php

namespace Biztech\Translator\Model\ResourceModel\ConsoleProduct;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Biztech\Translator\Model\ConsoleProduct', 'Biztech\Translator\Model\ResourceModel\ConsoleProduct');
    }
}
