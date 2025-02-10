<?php

namespace Meetanshi\ImageClean\Model;

use Magento\Framework\Model\AbstractModel;

class Images extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Meetanshi\ImageClean\Model\ResourceModel\Images');
    }
}
