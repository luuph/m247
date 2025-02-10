<?php
namespace MageArray\Popup\Model\ResourceModel\Popup;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package MageArray\Popup\Model\ResourceModel\Popup
 */
class Collection extends AbstractCollection
{

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(
            \MageArray\Popup\Model\Popup::Class,
            \MageArray\Popup\Model\ResourceModel\Popup::Class
        );
    }
}
