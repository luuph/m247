<?php

namespace Meetanshi\ImageClean\Model\ResourceModel\Imageclean;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ProductFactory;
use Meetanshi\ImageClean\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Collection extends AbstractCollection
{
    protected $idFieldName = 'imageclean_id';
    protected $total;
    private $productFactory;
    protected $dateTime;
    protected $helper;
    protected $productCollection;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        ProductFactory $productFactory,
        Data $data,
        CollectionFactory $collectionFactory
    ) {

        $this->productFactory = $productFactory;
        $this->helper = $data;
        $this->productCollection = $collectionFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager);
    }

    public function _construct()
    {
        $this->_init('Meetanshi\ImageClean\Model\Imageclean', 'Meetanshi\ImageClean\Model\ResourceModel\Imageclean');
    }

    public function getImages()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $array = [];

        try {
            $this->setConnection($this->getResource()->getConnection());
            $this->getSelect()->from(
                ['main_table' => $this->getTable('catalog_product_entity_media_gallery')], '*')
                ->group(['value_id']);

            $this->getSelect()->joinLeft(
                ['gallery_value_to_entity' => $this->getTable('catalog_product_entity_media_gallery_value_to_entity')],
                'main_table.value_id = gallery_value_to_entity.value_id',
                ['entity_id']
            );

            $collection = $this->productCollection->create();
            $collection->addAttributeToSelect('entity_id');
            $collection->addAttributeToSelect('name');

            $pid = [];
            foreach ($collection as $product){
                $pid[$product->getData('entity_id')] = $product->getName();
            }

            foreach ($this->getData() as $item) {
                $used = 1;
                if (array_key_exists($item['entity_id'],$pid)){
                    $array[$item['value']] = array('used' => $used,'file' => $item['value'],'productId' => $item['entity_id'],'product_name' => $pid[$item['entity_id']]);
                }else{
                    $array[$item['value']] = array('used' => $used,'file' => $item['value'],'productId' => $item['entity_id'],'product_name' => '');
                }
            }
        } catch (\Exception $e) {
            $logger->info($e->getMessage());
        }
        return $array;
    }
}
