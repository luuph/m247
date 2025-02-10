<?php

namespace Meetanshi\ImageClean\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\Observer;
use Meetanshi\ImageClean\Model\ResourceModel\Imageclean\CollectionFactory as ImagecleanFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;

class ProductDelete implements ObserverInterface
{
    private $storeManager;
    private $modelImagecleanFactory;
    protected $request;

    public function __construct(
        StoreManagerInterface $storeManager,
        ImagecleanFactory $modelImagecleanFactory,
        RequestInterface $request
    ) {
        $this->storeManager = $storeManager;
        $this->modelImagecleanFactory = $modelImagecleanFactory;
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        $productIds = $this->request->getPost();

        try {
            foreach ($productIds['selected'] as $productId) {
                $model = $this->modelImagecleanFactory->create()
                    ->addFieldToFilter('product_id',$productId);

                foreach($model as $item){
                    $item->setData('used',0);
                    $item->save();
                }
            }
        } catch (\Exception $e) {
           ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info($e->getMessage());
        }
    }
}
