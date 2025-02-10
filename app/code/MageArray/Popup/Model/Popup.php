<?php
namespace MageArray\Popup\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Popup
 * @package MageArray\Popup\Model
 */
class Popup extends AbstractModel
{

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\MageArray\Popup\Model\ResourceModel\Popup::Class);
    }
}
