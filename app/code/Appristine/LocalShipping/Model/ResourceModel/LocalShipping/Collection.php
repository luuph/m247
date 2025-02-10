<?php

namespace Appristine\LocalShipping\Model\ResourceModel\LocalShipping;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
   
    public function _construct()
    {
        $this->_init('Appristine\LocalShipping\Model\LocalShipping', 'Appristine\LocalShipping\Model\ResourceModel\LocalShipping');
    }
}
