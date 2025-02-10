<?php

namespace Meetanshi\ImageClean\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Imageclean extends AbstractDb
{

    protected function _construct()
    {
        $this->_init('meetanshi_imageclean', 'imageclean_id');
    }
}
