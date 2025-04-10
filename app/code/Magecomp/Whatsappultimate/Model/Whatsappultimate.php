<?php
namespace Magecomp\Whatsappultimate\Model;

use Magento\Framework\Model\AbstractModel;

class Whatsappultimate extends AbstractModel
{
    protected function _construct()
    {
        $this->_init("Magecomp\Whatsappultimate\Model\ResourceModel\Whatsappultimate");
    }
}
