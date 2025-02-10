<?php

namespace Meetanshi\ImageClean\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'imageclean_id';

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager
    ) {
        $this->_init('Meetanshi\ImageClean\Model\Images', 'Meetanshi\ImageClean\Model\ResourceModel\Images');
        $this->storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager);
    }
    protected function _construct()
    {
        parent::_construct();
    }
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->where('main_table.isproduct = "0"');
    }
}

