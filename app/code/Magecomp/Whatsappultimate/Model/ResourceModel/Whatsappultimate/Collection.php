<?php
namespace Magecomp\Whatsappultimate\Model\ResourceModel\Whatsappultimate;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init("Magecomp\Whatsappultimate\Model\Whatsappultimate", "Magecomp\Whatsappultimate\Model\ResourceModel\Whatsappultimate");
    }
}
